<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

final class PluginPathHandler
{
    private const MAIN_PLUGIN_FILE = 'samedaycourier-shipping.php';

    /**
     * @var string|null
     */
    private static ?string $root = null;

    /**
     * @param string $mainFile Absolute path to samedaycourier-shipping.php.
     *
     * @return void
     */
    public static function bootstrap(string $mainFile): void
    {
        self::$root = plugin_dir_path($mainFile);
    }

    /**
     * @return string
     */
    public static function root(): string
    {
        if (null === self::$root) {
            self::$root = dirname(__DIR__, 4) . '/';
        }

        return self::$root;
    }

    /**
     * @param string $relativePath Path relative to the plugin root.
     *
     * @return string
     */
    public static function to(string $relativePath = ''): string
    {
        if ('' === $relativePath) {
            return self::root();
        }

        return self::root() . ltrim(str_replace('\\', '/', $relativePath), '/');
    }

    /**
     * @return string
     */
    public static function mainFile(): string
    {
        return self::to(self::MAIN_PLUGIN_FILE);
    }
}
