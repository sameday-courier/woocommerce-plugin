<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use WC_Cache_Helper;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshShippingMethodsAction extends AbstractAction
{
    // Enabling, disabling and refreshing session shipping methods data
    private const ACTION_NAME = 'woocommerce_checkout_update_order_review';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION_NAME;
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        foreach (WooHandler::getWC()->cart->get_shipping_packages() as $package_key => $package) {
            $packageHash = sprintf(
                'wc_ship_%s',
                md5( wp_json_encode($package) . WC_Cache_Helper::get_transient_version('shipping'))
            );
            $package['package_hash'] = $packageHash;
            WooSessionHandler::set(SamedaySessionKeys::shippingForPackage((int) $package_key), $package);
        }

        WooHandler::getWC()->cart->calculate_shipping();
    }
}
