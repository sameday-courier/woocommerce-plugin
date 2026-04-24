<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Sql;

if (!defined( 'ABSPATH')) {
    exit;
}

class PluginHandler
{
    public static function install(): void
    {
        (new Installer())->run();
    }

    /**
     * @return void
     */
    public static function uninstall(): void
    {
        (new Uninstaller())->run();
    }
}

