<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Services;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\AddNewParcelAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\GenerateAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\RemoveAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\ShowAsPdfAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City\GetCitiesController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City\RefreshCityController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\County\GetCountiesController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Import\AllImportController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Locker\ChangeLockerController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Locker\RefreshLockerController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint\AddNewPickupPointController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint\DeletePickupPointController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint\RefreshPickupPointController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Service\EditServiceController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Service\RefreshServiceController;

if (!defined('ABSPATH')) {
    exit;
}

class ControllersRegisterService
{
    private const POST_CONTROLLERS = 'admin_post_';
    private const AJAX_CONTROLLERS = 'wp_ajax_';
    private const CONTROLLERS =
    [
        self::POST_CONTROLLERS =>
        [
            RemoveAwbController::class,
            ShowAsPdfAwbController::class,
            AddNewParcelAwbController::class,
            EditServiceController::class,
            RefreshServiceController::class,
            RefreshPickupPointController::class,
            RefreshLockerController::class,
            GenerateAwbController::class,
            AddNewPickupPointController::class,
            DeletePickupPointController::class,
        ],
        self::AJAX_CONTROLLERS =>
        [
            AllImportController::class,
            RefreshCityController::class,
            GetCountiesController::class,
            GetCitiesController::class,
            ChangeLockerController::class,
        ]
    ];

    /**
     * @return void
     */
    public static function register(): void
    {
        foreach (self::CONTROLLERS as $controllersType => $controllers) {
            foreach ($controllers as $controller) {
                $controller = new $controller();
                if ($controller instanceof AbstractController) {
                    self::addHook($controllersType, $controller);
                }
            }

        }
    }

    /**
     * @param string $hookName
     * @param AbstractController $controller
     *
     * @return void
     */
    private static function addHook(string $hookName, AbstractController $controller): void
    {
        add_action(
            $hookName . $controller->getAction(),
            [
                $controller,
                'handle'
            ]
        );
    }
}
