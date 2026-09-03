<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\LockerServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

final class LockerDtoFactoryFactory
{
    /**
     * @param DbHandler|null $dbHandler
     *
     * @return LockerDtoFactory
     */
    public static function create(?DbHandler $dbHandler = null): LockerDtoFactory
    {
        $resolvedDbHandler = $dbHandler ?? new DbHandler();

        return new LockerDtoFactory(
            new LockerServiceProvider(new SamedayLockerRepository($resolvedDbHandler))
        );
    }
}
