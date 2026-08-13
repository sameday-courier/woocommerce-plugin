<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories;

use InvalidArgumentException;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\PackageType;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Domain\Ports\GenerateAwbOrderProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OpenPackageOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderWeightCalculatorInterface;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class GenerateAwbItemFactory
{
    private GenerateAwbOrderProviderInterface $orderProvider;

    private OrderWeightCalculatorInterface $orderWeightCalculator;

    private OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler;

    private SamedayPickupPointRepository $samedayPickupPointRepository;

    private SamedayServiceRepository $samedayServiceRepository;

    private SamedayServiceRules $samedayServiceRules;

    private LockerDtoFactory $lockerDtoFactory;

    public function __construct(
        GenerateAwbOrderProviderInterface $orderProvider,
        OrderWeightCalculatorInterface $orderWeightCalculator,
        OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler,
        SamedayPickupPointRepository $samedayPickupPointRepository,
        SamedayServiceRepository $samedayServiceRepository,
        SamedayServiceRules $samedayServiceRules,
        ?LockerDtoFactory $lockerDtoFactory = null
    ) {
        $this->orderProvider = $orderProvider;
        $this->orderWeightCalculator = $orderWeightCalculator;
        $this->openPackageOrderDataHandler = $openPackageOrderDataHandler;
        $this->samedayPickupPointRepository = $samedayPickupPointRepository;
        $this->samedayServiceRules = $samedayServiceRules;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->lockerDtoFactory = $lockerDtoFactory ?? new LockerDtoFactory();
    }

    /**
     * Builds a GenerateAwbItem from order defaults (bulk flow).
     *
     * @throws InvalidArgumentException
     */
    public function fromOrderId(int $orderId): GenerateAwbItem
    {
        $order = $this->orderProvider->getById($orderId);
        if (null === $order) {
            throw new InvalidArgumentException(
                sprintf('Order #%d could not be found.', $orderId)
            );
        }

        $pickupPointId = $this->samedayPickupPointRepository->getDefaultPickupPointId();
        if (null === $pickupPointId) {
            throw new InvalidArgumentException('Default pickup point could not be found.');
        }

        if ([] === $order->getShippingLines()) {
            throw new InvalidArgumentException(
                sprintf('Order #%d has no shipping lines.', $orderId)
            );
        }

        $serviceId = $this->resolveServiceId(
            $order->getSamedayServiceCode(),
            $order->getLocker()
        );
        if (null === $serviceId) {
            throw new InvalidArgumentException(
                sprintf('Sameday service could not be resolved for order #%d.', $orderId)
            );
        }

        $repayment = $order->getOrderTotal();
        if ($order->getPaymentMethodId() !== SamedayConstants::CASH_ON_DELIVERY) {
            $repayment = 0;
        }

        $inputParams = [
            'samedaycourier-order-id' => $orderId,
            'samedaycourier-service' => $serviceId,
            'samedaycourier-package-pickup-point' => $pickupPointId,
            'shipping_lines' => $order->getShippingLines(),
            'shipping' => $order->getShipping(),
            'billing' => $order->getBilling(),
            'locker' => $order->getLocker(),
            'samedaycourier-package-type' => PackageType::PARCEL,
            'samedaycourier-package-awb-payment' => AwbPaymentType::CLIENT,
            'samedaycourier-package-insurance-value' => 0,
            'samedaycourier-package-repayment' => $repayment,
            'samedaycourier-client-reference' => $order->getOrderNumber(),
            'samedaycourier-package-dimensions' => $this->orderWeightCalculator->toPackageDimensions($orderId),
        ];

        if ($this->openPackageOrderDataHandler->isEnabled($orderId)) {
            $inputParams['samedaycourier-open-package-status'] = 'on';
        }

        return GenerateAwbItem::fromArray($inputParams);
    }

    /**
     * @param mixed $locker
     */
    private function resolveServiceId(?string $serviceCode, $locker): ?int
    {
        if (null === $serviceCode || '' === $serviceCode) {
            return null;
        }

        $lockerDto = $this->lockerDtoFactory->fromInput($locker);

        if (
            null !== $lockerDto
            && '1' === $lockerDto->getOohType()
            && $this->samedayServiceRules->isOohDeliveryOptionByCode($serviceCode)
        ) {
            $serviceCode = SamedayConstants::OOH_TYPES[1];
        }

        $service = $this->samedayServiceRepository->getServiceSamedayByCode($serviceCode);

        return null !== $service ? $service->getSamedayId() : null;
    }
}
