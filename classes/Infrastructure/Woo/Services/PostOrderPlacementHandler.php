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
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\LockerDtoFactoryFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use WC_Order;

final class PostOrderPlacementHandler
{
    private OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider;

    private CarrierServiceFromShippingLineResolver $carrierServiceFromShippingLineResolver;

    private CarrierServiceRules $carrierServiceRules;

    private LockerOrderDataHandlerInterface $lockerOrderDataHandler;

    private OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler;

    private SessionHandlerInterface $sessionHandler;

    /**
     * @param ?OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider
     * @param ?SamedayServiceRepository $samedayServiceRepository
     * @param ?CarrierServiceRules $carrierServiceRules
     * @param ?LockerOrderDataHandlerInterface $lockerOrderDataHandler
     * @param ?OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler
     * @param ?SessionHandlerInterface $sessionHandler
     * @param ?CarrierServiceFromShippingLineResolver $carrierServiceFromShippingLineResolver
     */
    public function __construct(
        ?OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider = null,
        ?SamedayServiceRepository $samedayServiceRepository = null,
        ?CarrierServiceRules $carrierServiceRules = null,
        ?LockerOrderDataHandlerInterface $lockerOrderDataHandler = null,
        ?OpenPackageOrderDataHandlerInterface $openPackageOrderDataHandler = null,
        ?SessionHandlerInterface $sessionHandler = null,
        ?CarrierServiceFromShippingLineResolver $carrierServiceFromShippingLineResolver = null
    ) {
        $samedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository();
        $this->carrierServiceFromShippingLineResolver = $carrierServiceFromShippingLineResolver
            ?? new CarrierServiceFromShippingLineResolver($samedayServiceRepository);
        $this->carrierServiceRules = $carrierServiceRules ?? new CarrierServiceRules($samedayServiceRepository);
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
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return;
        }

        $this->applyOrderShippingChanges($order);

        $this->openPackageOrderDataHandler->saveFromSession($order->get_id());
    }

    /**
     * @param WC_Order $order
     *
     * @return void
     */
    private function applyOrderShippingChanges(WC_Order $order): void
    {
        $shippingLines = $order->get_items('shipping');
        if ([] === $shippingLines) {
            return;
        }

        $service = $this->carrierServiceFromShippingLineResolver->resolve($shippingLines);
        if (null === $service) {
            return;
        }

        if ($this->carrierServiceRules->isOohDeliveryOptionByCode($service->getSamedayCode())) {
            try {
                $locker = $this->sessionHandler->get(CarrierSessionKeys::LOCKER);
                if (
                    null !== $locker
                    && '' !== $locker
                    && null === LockerDtoFactoryFactory::create()->fromInput($locker)
                ) {
                    $this->sessionHandler->set(CarrierSessionKeys::LOCKER, '');
                }

                $this->lockerOrderDataHandler->add(
                    $order->get_id(),
                    $locker
                );
            } catch (Exception $exception) {
            }
        }

        $this->orderShippingChangesServiceProvider->apply(
            new OrderShippingChangesRequestDto(
                $order->get_id(),
                $service,
                $shippingLines
            )
        );
    }
}
