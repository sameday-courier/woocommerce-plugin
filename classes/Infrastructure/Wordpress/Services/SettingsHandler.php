<?php

namespace SamedayCourier\Shipping\Infrastructure\Wordpress;

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
