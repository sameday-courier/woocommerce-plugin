<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwb;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;

final class AddNewParcelAwbFactory
{
    /**
     * @return AddNewParcelAwb
     */
    public static function create(): AddNewParcelAwb
    {
        return new AddNewParcelAwb(
            new OrderAwbStoreServiceProvider(),
            new CourierServiceProvider()
        );
    }
}
