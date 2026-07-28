<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use WooCommerce;

if (!defined('ABSPATH')) {
    exit;
}

class WooHandler
{
    /**
     * @var string|null
     */
    private static string $pluginVersion;

    /**
     * @return WooCommerce
     */
    public static function getWC(): WooCommerce
    {
        return WC();
    }

    /**
     * @return string
     */
    public static function getPlatformVersion(): string
    {
        return self::getWC()->version;
    }

    /**
     * @return string
     */
    public static function getPluginMainFile(): string
    {
        return SAMEDAYCOURIER_SHIPPING_PLUGIN_PATH . 'samedaycourier-shipping.php';
    }

    /**
     * @return string
     */
    public static function getPluginVersion(): string
    {
        if (null !== self::$pluginVersion) {
            return self::$pluginVersion;
        }

        $pluginData = get_file_data(
            self::getPluginMainFile(),
            ['Version' => 'Version'],
            'plugin'
        );

        self::$pluginVersion = $pluginData['Version'] ?? '';

        return self::$pluginVersion;
    }
}
