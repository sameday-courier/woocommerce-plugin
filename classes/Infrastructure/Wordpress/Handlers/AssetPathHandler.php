<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;

final class AssetPathHandler
{
    /**
     * @var string|null
     */
    private static ?string $fallbackVersion = null;

    /**
     * @param string $relativePath
     *
     * @return string
     */
    public static function url(string $relativePath): string
    {
        return plugins_url($relativePath, (new WooHandler())->getPluginMainFile());
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    public static function version(string $relativePath): string
    {
        $absolutePath = PluginPathHandler::to($relativePath);

        if (file_exists($absolutePath)) {
            return (string) filemtime($absolutePath);
        }

        return self::fallbackVersion();
    }

    /**
     * @return string
     */
    private static function fallbackVersion(): string
    {
        if (null === self::$fallbackVersion) {
            self::$fallbackVersion = (new WooHandler())->getPluginVersion();
        }

        return self::$fallbackVersion;
    }
}
