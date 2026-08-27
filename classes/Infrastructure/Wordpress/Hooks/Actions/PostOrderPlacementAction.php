<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Exception;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\DTOs\Requests\OrderShippingChangesRequestDto;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingChangesServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooLockerOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderShippingChangesServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use WC_Order;

final class PostOrderPlacementAction extends AbstractAction
{
    private const ACTION = 'woocommerce_checkout_order_processed';

    private OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider;

    private WooShippingMethodProvider $wooShippingMethodProvider;

    private SamedayServiceRepository $samedayServiceRepository;

    private CarrierServiceRules $carrierServiceRules;

    /**
     * @param ?OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider
     * @param ?WooShippingMethodProvider $wooShippingMethodProvider
     * @param ?SamedayServiceRepository $samedayServiceRepository
     * @param ?CarrierServiceRules $carrierServiceRules
     */
    public function __construct(
        ?OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider = null,
        ?WooShippingMethodProvider $wooShippingMethodProvider = null,
        ?SamedayServiceRepository $samedayServiceRepository = null,
        ?CarrierServiceRules $carrierServiceRules = null
    ) {
        $this->samedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository();
        $this->wooShippingMethodProvider = $wooShippingMethodProvider ?? new WooShippingMethodProvider();
        $this->carrierServiceRules = $carrierServiceRules ?? new CarrierServiceRules($this->samedayServiceRepository);
        $this->orderShippingChangesServiceProvider = $orderShippingChangesServiceProvider
            ?? new OrderShippingChangesServiceProvider(null, null, $this->carrierServiceRules);
    }

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @return string[]|null
     */
    public function getParams(): ?array
    {
        return ['orderId'];
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        if (null === $orderId = $args[0] ?? null) {
            return;
        }

        $this->applyOrderShippingChanges((int) $orderId);

        if ($this->isOutOfHomeDelivery()) {
            try {
                (new WooLockerOrderDataHandler())->add(
                    $orderId,
                    (new WooSessionHandler())->get(CarrierSessionKeys::LOCKER)
                );
            } catch (Exception $exception) {
            }
        }

        (new WooOpenPackageOrderDataHandler())->saveFromSession($orderId);
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
