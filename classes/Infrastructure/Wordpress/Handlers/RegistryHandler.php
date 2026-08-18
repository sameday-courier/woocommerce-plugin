<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\Services\ActionsRegisterService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters\Services\FiltersRegisterService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Loaders\Services\LoadersRegisterService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Services\ControllersRegisterService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\RegistryHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;

final class RegistryHandler
{
    private const REGISTERS = [
        ControllersRegisterService::class,
        ActionsRegisterService::class,
        FiltersRegisterService::class,
        CssStylesheetsHandler::class,
        JsScriptsHandler::class,
        LoadersRegisterService::class,
        NoticerHandler::class,
    ];

    /**
     * @return void
     */
    public static function register(): void
    {
        foreach (self::REGISTERS as $class) {
            /** @var RegistryHandlerInterface $register */
            $register = new $class();
            if ($register instanceof RegistryHandlerInterface) {
                $register->register();
            }
        }
    }
}
