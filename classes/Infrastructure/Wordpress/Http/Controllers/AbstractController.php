<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Traits\HandlesControllerAccessTrait;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Traits\JsonResponseTrait;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\UrlsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\UserPermissionChecker;

abstract class AbstractController implements ControllerInterface
{
    use JsonResponseTrait;
    use HandlesControllerAccessTrait;

    /**
     * @return void
     */
    public function handle(): void
    {
        $inputParams = InputSanitizer::sanitizeInputs($_POST);

        if (!UserPermissionChecker::canAccess()) {
            $this->denyAccess(
                TranslatorHandler::translate('Not enough permission to access this content.')
            );
        }

        if (!NonceHandler::verify($inputParams['_wpnonce'] ?? '', $this->getAction())) {
            $this->denyAccess(
                TranslatorHandler::translate('Invalid nonce.')
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
     * @param string $mainPath
     * @param array $queryArgs
     *
     * @return void
     */
    protected function redirectTo(string $mainPath, array $queryArgs = []): void
    {
        wp_safe_redirect(UrlsHandler::build($mainPath, $queryArgs));

        exit;
    }

    /**
     * @param array $inputParams
     *
     * @return void
     */
    abstract protected function processAction(array $inputParams): void;
}
