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
    /**
     * @var list<class-string<RepositoryInterface>>
     */
    private const SAMEDAY_REPOSITORIES = [
        SamedayAwbRepository::class,
        SamedayLockerRepository::class,
        SamedayPickupPointRepository::class,
        SamedayServiceRepository::class,
        SamedayPackageRepository::class,
        SamedayCityRepository::class,
    ];

    /**
     * @return list<string>
     */
    public function getSamedayTables(): array
    {
        $tables = [];
        foreach (self::SAMEDAY_REPOSITORIES as $repositoryClass) {
            $tables[] = (new $repositoryClass())->getTableName();
        }

        return $tables;
    }
}
