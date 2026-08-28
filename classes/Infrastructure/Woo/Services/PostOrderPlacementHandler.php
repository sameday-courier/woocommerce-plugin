<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use Exception;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\DTOs\Requests\OrderShippingChangesRequestDto;
use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\OpenPackageOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingChangesServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderShippingChangesServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use WC_Order;

final class PostOrderPlacementHandler
{
    private OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider;

    private WooShippingMethodProvider $wooShippingMethodProvider;

    private SamedayServiceRepository $samedayServiceRepository;

    private CarrierServiceRules $carrierServiceRules;

    private LockerOrderDataHandlerInterface $lockerOrderDataHandler;

    private OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler;

    private SessionHandlerInterface $sessionHandler;

    /**
     * @param ?OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider
     * @param ?WooShippingMethodProvider $wooShippingMethodProvider
     * @param ?SamedayServiceRepository $samedayServiceRepository
     * @param ?CarrierServiceRules $carrierServiceRules
     * @param ?LockerOrderDataHandlerInterface $lockerOrderDataHandler
     * @param ?OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler
     * @param ?SessionHandlerInterface $sessionHandler
     */
    public function __construct(
        ?OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider = null,
        ?WooShippingMethodProvider $wooShippingMethodProvider = null,
        ?SamedayServiceRepository $samedayServiceRepository = null,
        ?CarrierServiceRules $carrierServiceRules = null,
        ?LockerOrderDataHandlerInterface $lockerOrderDataHandler = null,
        ?OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler = null,
        ?SessionHandlerInterface $sessionHandler = null
    ) {
        $this->samedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository();
        $this->wooShippingMethodProvider = $wooShippingMethodProvider ?? new WooShippingMethodProvider();
        $this->carrierServiceRules = $carrierServiceRules ?? new CarrierServiceRules($this->samedayServiceRepository);
        $this->orderShippingChangesServiceProvider = $orderShippingChangesServiceProvider
            ?? new OrderShippingChangesServiceProvider(null, null, $this->carrierServiceRules);
        $this->lockerOrderDataHandler = $lockerOrderDataHandler ?? new WooLockerOrderDataHandler();
        $this->openPackageOrderDataHandler = $openPackageOrderDataHandler ?? new WooOpenPackageOrderDataHandler();
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function handle(int $orderId): void
    {
        $this->applyOrderShippingChanges($orderId);

        if ($this->isOutOfHomeDelivery()) {
            try {
                $this->lockerOrderDataHandler->add(
                    $orderId,
                    $this->sessionHandler->get(CarrierSessionKeys::LOCKER)
                );
            } catch (Exception $exception) {
            }
        }

        $this->openPackageOrderDataHandler->saveFromSession($orderId);
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    private function applyOrderShippingChanges(int $orderId): void
    {
        $serviceCode = $this->wooShippingMethodProvider->getChosenServiceCode();
        if ('' === $serviceCode) {
            return;
        }

        $service = $this->samedayServiceRepository->getServiceSamedayByCode($serviceCode);
        if (null === $service) {
            return;
        }

        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return;
        }

        $shippingLines = $order->get_items('shipping');
        if ([] === $shippingLines) {
            return;
        }

        $this->orderShippingChangesServiceProvider->apply(
            new OrderShippingChangesRequestDto(
                $orderId,
                $service,
                $shippingLines
            )
        );
    }

    /**
     * @return bool
     */
    private function isOutOfHomeDelivery(): bool
    {
        return $this->carrierServiceRules->isOohDeliveryOptionByCode(
            $this->wooShippingMethodProvider->getChosenServiceCode()
        );
    }
}
