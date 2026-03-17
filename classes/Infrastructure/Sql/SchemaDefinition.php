<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

use SamedayCourier\Shipping\Infrastructure\Sql\Repository\RepositoryInterface;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayPackageRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayServiceRepository;

class SchemaDefinition
{
    private const SAMEDAY_REPOSITORIES = [
        SamedayAwbRepository::class,
        SamedayLockerRepository::class,
        SamedayPickupPointRepository::class,
        SamedayServiceRepository::class,
        SamedayPackageRepository::class,
        SamedayCityRepository::class,
    ];

    /**
     * @return string[]
     */
    public static function getSamedayTables(): array
    {
        return array_map(
            static function (RepositoryInterface $repo) {
                return $repo::getTableName();
            },
            self::SAMEDAY_REPOSITORIES
        );
    }
}
