<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\RepositoryInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPackageRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

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
    public function getSamedayTables(): array
    {
        return array_map(
            /**
             * @param RepositoryInterface $repo
             *
             * @return mixed
             */
            static function (RepositoryInterface $repo) {
                return $repo->getTableName();
            },
            self::SAMEDAY_REPOSITORIES
        );
    }
}
