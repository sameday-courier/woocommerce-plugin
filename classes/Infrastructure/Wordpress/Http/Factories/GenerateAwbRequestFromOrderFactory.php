<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use InvalidArgumentException;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbRequest;
use SamedayCourier\Shipping\Domain\CarrierAwbPaymentTypes;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierPackageTypes;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\Ports\GenerateAwbOrderProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OpenPackageOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderWeightCalculatorInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\GenerateAwbMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class GenerateAwbRequestFromOrderFactory
{
    /**
     * @var GenerateAwbOrderProviderInterface $orderProvider
     */
    private GenerateAwbOrderProviderInterface $orderProvider;

    /**
     * @var OrderWeightCalculatorInterface $orderWeightCalculator
     */
    private OrderWeightCalculatorInterface $orderWeightCalculator;

    /**
     * @var OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler
     */
    private OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var CarrierServiceRules $carrierServiceRules
     */
    private CarrierServiceRules $carrierServiceRules;

    /**
     * @var LockerDtoFactory $lockerDtoFactory
     */
    private LockerDtoFactory $lockerDtoFactory;

    /**
     * @param GenerateAwbOrderProviderInterface $orderProvider
     * @param OrderWeightCalculatorInterface $orderWeightCalculator
     * @param OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler
     * @param SamedayPickupPointRepository $samedayPickupPointRepository
     * @param SamedayServiceRepository $samedayServiceRepository
     * @param CarrierServiceRules $carrierServiceRules
     * @param LockerDtoFactory|null $lockerDtoFactory
     */
    public function __construct(
        GenerateAwbOrderProviderInterface $orderProvider,
        OrderWeightCalculatorInterface $orderWeightCalculator,
        OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler,
        SamedayPickupPointRepository $samedayPickupPointRepository,
        SamedayServiceRepository $samedayServiceRepository,
        CarrierServiceRules $carrierServiceRules,
        ?LockerDtoFactory $lockerDtoFactory = null
    ) {
        $this->orderProvider = $orderProvider;
        $this->orderWeightCalculator = $orderWeightCalculator;
        $this->openPackageOrderDataHandler = $openPackageOrderDataHandler;
        $this->samedayPickupPointRepository = $samedayPickupPointRepository;
        $this->carrierServiceRules = $carrierServiceRules;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->lockerDtoFactory = $lockerDtoFactory ?? new LockerDtoFactory();
    }

    /**
     * Builds a GenerateAwbRequest from order defaults (bulk flow).
     *
     * @param int $orderId
     *
     * @return GenerateAwbRequest
     *
     * @throws InvalidArgumentException
     */
    public function fromOrderId(int $orderId): GenerateAwbRequest
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
        if ($order->getPaymentMethodId() !== CarrierConstants::CASH_ON_DELIVERY) {
            $repayment = 0;
        }

        $inputParams = [
            GenerateAwbMapper::ORDER_ID_KEY => $orderId,
            GenerateAwbMapper::SERVICE_ID_KEY => $serviceId,
            GenerateAwbMapper::PICKUP_POINT_ID_KEY => $pickupPointId,
            GenerateAwbMapper::SHIPPING_LINES_KEY => $order->getShippingLines(),
            GenerateAwbMapper::SHIPPING_KEY => $order->getShipping(),
            GenerateAwbMapper::BILLING_KEY => $order->getBilling(),
            GenerateAwbMapper::LOCKER_KEY => $order->getLocker(),
            GenerateAwbMapper::PACKAGE_TYPE_KEY => CarrierPackageTypes::PARCEL,
            GenerateAwbMapper::AWB_PAYMENT_KEY => CarrierAwbPaymentTypes::CLIENT,
            GenerateAwbMapper::INSURANCE_VALUE_KEY => 0,
            GenerateAwbMapper::REPAYMENT_KEY => $repayment,
            GenerateAwbMapper::CLIENT_REFERENCE_KEY => $order->getOrderNumber(),
            GenerateAwbMapper::PACKAGE_DIMENSIONS_KEY => $this->orderWeightCalculator->toPackageDimensions($orderId),
        ];

        if ($this->openPackageOrderDataHandler->isEnabled($orderId)) {
            $inputParams[GenerateAwbMapper::OPEN_PACKAGE_KEY] = 'on';
        }

        $mapper = new GenerateAwbMapper($inputParams);

        return new GenerateAwbRequest(
            $mapper->orderId(),
            $mapper->serviceId(),
            $mapper->pickupPointId(),
            $mapper->shippingLines(),
            $mapper->shipping(),
            $mapper->billing(),
            $mapper->locker(),
            $mapper->hasOpenPackage(),
            $mapper->hasLockerFirstMile(),
            $mapper->packageType(),
            $mapper->awbPayment(),
            $mapper->insuranceValue(),
            $mapper->repayment(),
            $mapper->clientReference(),
            $mapper->observation(),
            $mapper->packageDimensions()
        );
    }

    /**
     * @param string|null $serviceCode
     * @param mixed $locker
     *
     * @return int|null
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
            && $this->carrierServiceRules->isOohDeliveryOptionByCode($serviceCode)
        ) {
            $serviceCode = CarrierConstants::OOH_TYPES[1];
        }

        $service = $this->samedayServiceRepository->getServiceSamedayByCode($serviceCode);

        return null !== $service ? $service->getSamedayId() : null;
    }
}
