<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout;

use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractNoPrivController;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class StoreOpenPackageInSessionController extends AbstractNoPrivController
{
    private const ACTION = 'store_sameday_open_package_in_session';

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
        if (null === ($openPackage = $inputParams['open_package'] ?? null)) {
            return;
        }

        WooSessionHandler::set(
            SamedaySessionKeys::OPEN_PACKAGE,
            $openPackage
        );
    }
}
