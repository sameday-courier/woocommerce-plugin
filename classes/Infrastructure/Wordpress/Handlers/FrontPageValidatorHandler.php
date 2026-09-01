<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use WC_Blocks_Utils;

final class FrontPageValidatorHandler
{
    /**
     * @var bool|null
     */
    private static ?bool $checkoutUsesBlocks = null;

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isCheckoutPage(): bool
    {
        return is_checkout();
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isStrictCheckoutPage(): bool
    {
        global $wp;

        return self::isCheckoutPage()
            && empty($wp->query_vars['order-pay'])
            && !isset($wp->query_vars['order-received']);
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isCheckoutNomenclatorPage(): bool
    {
        return self::isCheckoutPage()
            && (new CarrierSettingsServiceProvider())->get()->isUseSamedayNomenclator();
    }

    /**
     * @return bool
     */
    public static function isBlocksCheckoutPage(): bool
    {
        return self::isStrictCheckoutPage() && self::checkoutUsesBlocks();
    }

    /**
     * @return bool
     */
    public static function isClassicCheckoutPage(): bool
    {
        return self::isStrictCheckoutPage() && !self::checkoutUsesBlocks();
    }

    /**
     * @return bool
     */
    private static function checkoutUsesBlocks(): bool
    {
        if (null !== self::$checkoutUsesBlocks) {
            return self::$checkoutUsesBlocks;
        }

        self::$checkoutUsesBlocks = false;

        if (method_exists(CartCheckoutUtils::class, 'is_checkout_block_default')) {
            self::$checkoutUsesBlocks = (bool) CartCheckoutUtils::is_checkout_block_default();
        } elseif (method_exists(WC_Blocks_Utils::class, 'has_block_in_page') && function_exists('wc_get_page_id')) {
            self::$checkoutUsesBlocks = (bool) WC_Blocks_Utils::has_block_in_page(
                wc_get_page_id('checkout'),
                'woocommerce/checkout'
            );
        }

        return self::$checkoutUsesBlocks;
    }
}
