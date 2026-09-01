<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwb;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostRemoveAwbServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class RemoveAwbFactory
{
    /**
     * @return RemoveAwb
     */
    public static function create(): RemoveAwb
    {
        $samedayAwbRepository = new SamedayAwbRepository();

        return new RemoveAwb(
            new OrderAwbStoreServiceProvider($samedayAwbRepository),
            new CourierServiceProvider(),
            new PostRemoveAwbServiceProvider($samedayAwbRepository)
        );
    }
}
