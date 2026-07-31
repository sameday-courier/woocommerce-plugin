<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Services\ControllersRegisterService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Interfaces\RegistryHandlerInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class RegistryHandler implements RegistryHandlerInterface
{
    private const Registers = [
        ControllersRegisterService::class,
        CssStylesheetsHandler::class,
        JsScriptsHandler::class,
    ];

    /**
     * @return void
     */
    public static function register(): void
    {
        foreach (self::Registers as $class) {
            if ($class instanceof RegistryHandlerInterface) {
                $class::register();
            }
        }
    }
}
