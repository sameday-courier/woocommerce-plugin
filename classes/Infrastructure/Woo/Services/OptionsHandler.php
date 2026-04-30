<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined( 'ABSPATH')) {
    exit;
}

class OptionsHandler
{
    private const WOO_SAMEDAY_OPTION_KEY = "woocommerce_samedaycourier_settings";

    /**
     * @return array
     */
    public static function getSamedayOptions(): array
    {
        $settings = self::getOption(self::WOO_SAMEDAY_OPTION_KEY);
        if (false === $settings) {
            return [];
        }

        return is_array($settings) ? $settings : [];
    }

    public static function setSamedayOptions(array $options): void
    {
        self::setOption(self::WOO_SAMEDAY_OPTION_KEY, $options);
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function getOption(string $key, $default = false)
    {
        return get_option($key, $default);
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return void
     */
    public static function setOption(string $key, $value): void
    {
        update_option($key, $value);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public static function removeOption(string $key): void
    {
        delete_option($key);
    }
}
