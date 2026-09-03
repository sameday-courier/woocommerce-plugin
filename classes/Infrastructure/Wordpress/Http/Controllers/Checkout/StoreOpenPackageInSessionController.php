<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout;

use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractNoPrivController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\OpenPackageSessionNormalizer;

final class StoreOpenPackageInSessionController extends AbstractNoPrivController
{
    private const ACTION = 'store_sameday_open_package_in_session';

    /**
     * @var WooSessionHandler $wooSession
     */
    private WooSessionHandler $sessionHandler;

    /**
     * @param WooSessionHandler|null $sessionHandler
     */
    public function __construct(
        ?WooSessionHandler $sessionHandler = null
    ) {
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
    }

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
        if (!array_key_exists('open_package', $inputParams)) {
            return;
        }

        $this->sessionHandler->set(
            CarrierSessionKeys::OPEN_PACKAGE,
            OpenPackageSessionNormalizer::normalize($inputParams['open_package'])
        );
    }
}
