<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwb;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;

final class ShowAsPdfAwbFactory
{
    /**
     * @return ShowAsPdfAwb
     */
    public static function create(): ShowAsPdfAwb
    {
        return new ShowAsPdfAwb(
            new OrderAwbStoreServiceProvider(),
            new CourierServiceProvider(),
            new CarrierSettingsServiceProvider()
        );
    }
}
