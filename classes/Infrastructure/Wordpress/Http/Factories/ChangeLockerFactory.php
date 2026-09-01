<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Locker\Change\ChangeLocker;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooLockerOrderDataHandler;

final class ChangeLockerFactory
{
    /**
     * @return ChangeLocker
     */
    public static function create(): ChangeLocker
    {
        return new ChangeLocker(
            new WooLockerOrderDataHandler(),
        );
    }
}
