<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

if (!defined('ABSPATH')) {
    exit;
}

class EditServiceController extends AbstractController
{
    private const ACTION = 'sameday_edit_service';

    public function getAction(): string
    {
        return self::ACTION;
    }

    public function processPostAction(array $inputParams): void
    {
        if (!($_POST['action'] === self::ACTION)) {
            Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_services']);
        }
    }
}
