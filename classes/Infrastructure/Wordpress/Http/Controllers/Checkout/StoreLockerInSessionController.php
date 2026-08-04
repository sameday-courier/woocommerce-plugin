<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout;

use JsonException;
use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractNoPrivController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\InputSanitizer;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class StoreLockerInSessionController extends AbstractNoPrivController
{
    private const ACTION = 'store_sameday_locker_in_session';

    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array<string, mixed> $inputParams
     *
     * @return void
     */
    protected function processNoPrivAction(array $inputParams): void
    {
        if (null === $locker = $inputParams['locker'] ?? null) {
            return;
        }

        if (is_array($locker)) {
            try {
                $locker = InputSanitizer::sanitizeData($locker);
            } catch (JsonException $e) {
                return;
            }

            WooSessionHandler::set(SamedaySessionKeys::LOCKER, $locker);

            return;
        }

        WooSessionHandler::set(SamedaySessionKeys::LOCKER, (int) $locker);
    }
}
