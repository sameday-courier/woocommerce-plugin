<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory\ShowHistoryAwb;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PackageHistoryStoreServiceProvider;

final class ShowHistoryAwbFactory
{
    /**
     * @return ShowHistoryAwb
     */
    public static function create(): ShowHistoryAwb
    {
        return new ShowHistoryAwb(
            new OrderAwbStoreServiceProvider(),
            new CourierServiceProvider(),
            new PackageHistoryStoreServiceProvider()
        );
    }
}
