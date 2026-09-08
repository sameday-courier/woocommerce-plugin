<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbForm;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\AdminPageValidatorHandler;

final class ShowAddAwbModalContainerAction extends AbstractAction
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
        if (
            !AdminPageValidatorHandler::isOrdersListPage()
            && !AdminPageValidatorHandler::isOrderEditPage()
        ) {
            return;
        }

        echo sprintf('<div id="%s"></div>', esc_attr(AwbForm::ADD_AWB_MODAL_CONTAINER_ID));
    }
}
