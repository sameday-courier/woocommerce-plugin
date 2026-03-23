<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

if (!defined( 'ABSPATH')) {
	exit;
}

class PluginHandler
{
    public static function install(): void
    {
        Installer::run();
    }

    /**
     * @return void
     */
    public static function uninstall(): void
    {
        Uninstaller::run();
    }
}

