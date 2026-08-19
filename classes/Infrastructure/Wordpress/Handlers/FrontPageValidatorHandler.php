<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;

final class FrontPageValidatorHandler
{
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
}
