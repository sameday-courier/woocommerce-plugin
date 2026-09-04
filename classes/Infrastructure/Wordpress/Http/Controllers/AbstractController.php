<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Traits\HandlesControllerAccessTrait;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Traits\JsonResponseTrait;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Exceptions\RedirectDestinationNotFoundException;
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

        try {
            $this->processAction($inputParams);
        } catch (RedirectDestinationNotFoundException $exception) {
            $this->terminateAdminRequest($exception->getMessage());
        }
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
     * Redirect to an admin path or, when $mainPath is omitted, back to the initiating page.
     *
     * @param string|null $mainPath
     * @param array $queryArgs
     *
     * @return never
     */
    protected function redirectTo(?string $mainPath = null, array $queryArgs = []): void
    {
        if (null !== $mainPath && '' !== $mainPath) {
            $location = UrlsHandler::build($mainPath, $queryArgs);
        } else {
            $referer = wp_get_referer();
            $location = (false !== $referer && '' !== $referer) ? $referer : null;
        }

        if (null === $location) {
            throw new RedirectDestinationNotFoundException(
                TranslatorHandler::translate('Unable to determine where to redirect after this action.')
            );
        }

        wp_safe_redirect($location);
        exit();
    }

    /**
     * @param string $message
     * @param int $statusCode
     *
     * @return never
     */
    private function terminateAdminRequest(string $message, int $statusCode = 500): void
    {
        status_header($statusCode);
        nocache_headers();

        echo esc_html($message);
        exit;
    }

    /**
     * @param array $inputParams
     *
     * @return void
     */
    abstract protected function processAction(array $inputParams): void;
}
