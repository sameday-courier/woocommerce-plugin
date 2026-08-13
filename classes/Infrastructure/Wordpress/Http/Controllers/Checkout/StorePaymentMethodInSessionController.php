<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout;

use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractNoPrivController;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;

final class StorePaymentMethodInSessionController extends AbstractNoPrivController
{
    private const ACTION = 'store_sameday_payment_method_in_session';

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
        if (null === $paymentMethod = $inputParams['payment_method'] ?? null) {
            return;
        }

        (new WooSessionHandler())->set(
            SamedaySessionKeys::PAYMENT_METHOD,
            $paymentMethod
        );
    }
}
