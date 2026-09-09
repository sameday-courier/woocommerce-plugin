<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbForm;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class AwbFormFactory
{
    /**
     * @return AwbForm
     */
    public static function create(): AwbForm
    {
        return new AwbForm(
            new SamedayServiceRepository(),
            new SamedayLockerRepository(),
            new SamedayPickupPointRepository()
        );
    }
}
