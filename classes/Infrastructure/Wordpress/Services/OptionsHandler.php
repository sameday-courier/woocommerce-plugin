<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

if (!defined( 'ABSPATH')) {
    exit;
}

class OptionsHandler
{
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
}
