<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout;

use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractNoPrivController;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;

final class StoreOpenPackageInSessionController extends AbstractNoPrivController
{
    private const ACTION = 'store_sameday_open_package_in_session';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array $inputParams
     *
     * @return void
     */
    protected function processNoPrivAction(array $inputParams): void
    {
        if (null === ($openPackage = $inputParams['open_package'] ?? null)) {
            return;
        }

        (new WooSessionHandler())->set(
            CarrierSessionKeys::OPEN_PACKAGE,
            $openPackage
        );
    }
}
