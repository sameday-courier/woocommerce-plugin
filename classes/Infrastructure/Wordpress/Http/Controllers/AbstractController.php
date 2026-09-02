<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Exceptions\AccessDeniedException;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\UrlsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\UserPermissionChecker;

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
     * @param mixed $payload
     * @param int $statusCode
     *
     * @return void
     */
    protected function sendJsonErrorResponse(
        $payload,
        int $statusCode = 400
    ): void {
        wp_send_json_error($payload, $statusCode);
    }

    /**
     * @param mixed $payload
     * @param int $statusCode
     *
     * @return void
     */
    protected function sendJsonSuccessResponse(
        $payload,
        int $statusCode = 200
    ): void {
        wp_send_json_success($payload, $statusCode);
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
