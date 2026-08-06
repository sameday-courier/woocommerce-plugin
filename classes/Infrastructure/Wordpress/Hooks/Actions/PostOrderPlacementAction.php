<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use Exception;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Woo\WooOrderAddressRepository;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooLockerOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooLockerOrderPostMetaUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressArchive;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSamedayShippingHdAddressParser;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;

if (!defined('ABSPATH')) {
    exit;
}

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
                (new WooLockerOrderDataHandler(
                        new WooLockerOrderPostMetaUpdater(
                            new WooOrderShippingAddressUpdater(
                                new WooOrderAddressRepository(),
                                new WooOrderShippingAddressArchive(),
                                new SamedayLockerRepository(),
                                new WooSamedayShippingHdAddressParser(),
                            ),
                        ),
                    )
                )->add(
                    $orderId,
                    WooSessionHandler::get(SamedaySessionKeys::LOCKER)
                );
            } catch (Exception $exception) {}
        }

        WooOpenPackageOrderDataHandler::saveFromSession($orderId);
    }

    /**
     * @return bool
     */
    private function isOutOfHomeDelivery(): bool
    {
        return (new SamedayServiceRules(new SamedayServiceRepository()))
            ->isOohDeliveryOptionByCode(WooShippingMethodProvider::getChosenServiceCode());
    }
}

