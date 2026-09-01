<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\Services;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\AddExtraFeesAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\AdminOrderAddressUpdateAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\BlocksPostOrderPlacementAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\PostOrderPlacementAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\RefreshShippingMethodsAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\RegisterCheckoutBlocksIntegrationAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\RegisterOpenPackageBlocksIntegrationAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\RegisterOpenPackageCartUpdateCallbackAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\RenderAdminAwbFormsAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\ShowAdminOrderAwbActionsAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\ShowAwbNumberColumnInWcOrderGridAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\ShowBulkAwbButtonAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\ShowLockerFieldAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\ShowOpenPackageFieldAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\ValidateBlocksCheckoutLockerAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions\ValidateCheckoutLockerAction;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\ActionInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\RegistryHandlerInterface;

class ActionsRegisterService implements RegistryHandlerInterface
{
    private const ACTIONS = [
        AddExtraFeesAction::class,
        AdminOrderAddressUpdateAction::class,
        BlocksPostOrderPlacementAction::class,
        PostOrderPlacementAction::class,
        RefreshShippingMethodsAction::class,
        RegisterCheckoutBlocksIntegrationAction::class,
        RegisterOpenPackageBlocksIntegrationAction::class,
        RegisterOpenPackageCartUpdateCallbackAction::class,
        RenderAdminAwbFormsAction::class,
        ShowAdminOrderAwbActionsAction::class,
        ShowAwbNumberColumnInWcOrderGridAction::class,
        ShowBulkAwbButtonAction::class,
        ShowLockerFieldAction::class,
        ShowOpenPackageFieldAction::class,
        ValidateBlocksCheckoutLockerAction::class,
        ValidateCheckoutLockerAction::class,
    ];

    /**
     * @return void
     */
    public function register(): void
    {
        foreach (self::ACTIONS as $actionClass) {
            $action = new $actionClass();
            if ($action instanceof ActionInterface) {
                add_action(
                    $action->getActionName(),
                    static function (...$args) use ($action): void {
                        $action->handle(...$args);
                    },
                    $action->getPriority(),
                    $action->getAcceptedArgs()
                );
            }
        }
    }
}
