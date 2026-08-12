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

abstract class AbstractController implements ControllerInterface
{
    /**
     * @return void
     * @throws AccessDeniedException
     */
    public function handle(): void
    {
        $inputParams = InputSanitizer::sanitizeInputs($_POST);
        if (!UserPermissionChecker::canAccess()) {
            throw new AccessDeniedException(
                TranslatorHandler::translate("Not enough permission to access this content.")
            );
        }

        if (!NonceHandler::verify($inputParams['_wpnonce'], $this->getAction())) {
            throw new AccessDeniedException(
                TranslatorHandler::translate("Invalid nonce.")
            );
        }

        $this->processAction($inputParams);
    }

    /**
     * Current authenticated user id. Safe to call after handle() auth gate.
     *
     * @return int
     */
    protected function getCurrentUserId(): int
    {
        return UserPermissionChecker::getCurrentUserId();
    }

    /**
     * @param array $inputParams
     *
     * @return void
     */
    abstract protected function processAction(array $inputParams): void;
}
