<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Exception;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooLockerOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;

final class PostOrderPlacementAction extends AbstractAction
{
    private const ACTION = 'woocommerce_checkout_order_processed';

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
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        if (null === $orderId = $args[0] ?? null) {
            return;
        }

        if ($this->isOutOfHomeDelivery()) {
            try {
                (new WooLockerOrderDataHandler())->add(
                    $orderId,
                    (new WooSessionHandler())->get(CarrierSessionKeys::LOCKER)
                );
            } catch (Exception $exception) {}
        }

        (new WooOpenPackageOrderDataHandler())->saveFromSession($orderId);
    }

    /**
     * @return bool
     */
    private function isOutOfHomeDelivery(): bool
    {
        return (new CarrierServiceRules(new SamedayServiceRepository()))
            ->isOohDeliveryOptionByCode((new WooShippingMethodProvider())->getChosenServiceCode());
    }
}

