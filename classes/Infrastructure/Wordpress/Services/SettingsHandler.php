<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

if (!defined( 'ABSPATH')) {
    exit;
}

class SettingsHandler
{
    /**
     * @param string $key
     *
     * @return array
     */
    public static function getSettings(string $key): array
    {
        return get_option($key, []);
    }
}
