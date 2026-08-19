<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Services;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout\StoreLockerInSessionController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout\StoreOpenPackageInSessionController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout\StorePaymentMethodInSessionController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\ControllerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\AddNewParcelAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\BulkGenerateAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\BulkRemoveAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\GenerateAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\RemoveAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\ShowAsPdfAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\StartBulkGenerateAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb\StartBulkRemoveAwbController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City\GetCitiesController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\City\RefreshCityController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\County\GetCountiesController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Import\AllImportController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Import\StartAllImportController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Locker\ChangeLockerController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Locker\RefreshLockerController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint\AddNewPickupPointController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint\DeletePickupPointController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\PickupPoint\RefreshPickupPointController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Service\EditServiceController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Service\RefreshServiceController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\RegistryHandlerInterface;

class ControllersRegisterService implements RegistryHandlerInterface
{
    private const POST_CONTROLLERS = 'admin_post_';
    private const AJAX_CONTROLLERS = 'wp_ajax_';
    private const NO_PRIV_AJAX_CONTROLLERS = 'wp_ajax_nopriv_';
    private const METHOD = 'handle';

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
                RefreshCityController::class,
            ],
            self::AJAX_CONTROLLERS =>
            [
                GetCountiesController::class,
                GetCitiesController::class,
                ChangeLockerController::class,
                StartBulkGenerateAwbController::class,
                BulkGenerateAwbController::class,
                StartBulkRemoveAwbController::class,
                BulkRemoveAwbController::class,
                StartAllImportController::class,
                AllImportController::class,
                StoreLockerInSessionController::class,
                StoreOpenPackageInSessionController::class,
                StorePaymentMethodInSessionController::class,
            ],
            self::NO_PRIV_AJAX_CONTROLLERS =>
            [
                StoreLockerInSessionController::class,
                StoreOpenPackageInSessionController::class,
                StorePaymentMethodInSessionController::class,
            ]
        ];

    /**
     * @return void
     */
    public function register(): void
    {
        foreach (self::CONTROLLERS as $controllersType => $controllers) {
            foreach ($controllers as $controller) {
                $controller = new $controller();
                if ($controller instanceof ControllerInterface) {
                    self::addAction($controllersType, $controller);
                }
            }
        }
    }

    /**
     * @param string $actionName
     * @param ControllerInterface $controller
     *
     * @return void
     */
    /**
     * @param string $actionName
     * @param ControllerInterface $controller
     *
     * @return void
     */
    private static function addAction(string $actionName, ControllerInterface $controller): void
    {
        $hookName = $actionName . $controller->getAction();

        add_action(
            $hookName,
            [
                $controller,
                self::METHOD
            ]
        );
    }
}
