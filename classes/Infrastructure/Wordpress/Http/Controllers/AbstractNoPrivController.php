<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use Automattic\WooCommerce\EmailEditor\AccessDeniedException;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\UserPermissionChecker;

if (!defined("ABSPATH")) {
    exit;
}

abstract class AbstractNoPrivController implements ControllerInterface
{
    /**
     * @return void
     * @throws AccessDeniedException
     */
    public function handle(): void
    {
        $inputParams = InputSanitizer::sanitizeInputs($_POST);

        if (!NonceHandler::verify($inputParams['_wpnonce'], $this->getAction())) {
            throw new AccessDeniedException(
                TranslatorHandler::translate("Invalid nonce.")
            );
        }

        $this->processNoPrivAction($inputParams);
    }

    /**
     * @param array $inputParams
     *
     * @return void
     */
    abstract protected function processNoPrivAction(array $inputParams): void;
}
