<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\GenerateBulkAwbModal;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\RemoveBulkAwbModal;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\RemoveSingleAwbModal;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\AdminPageValidatorHandler;

final class ShowBulkAwbButtonAction extends AbstractAction
{
    private const ACTION = 'admin_footer';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        if (!AdminPageValidatorHandler::isOrdersListPage()) {
            return;
        }

        echo GenerateBulkAwbModal::render();
        echo RemoveBulkAwbModal::render();
        echo RemoveSingleAwbModal::render();
    }
}
