<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPackageRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\DbHandlerInterface;

class SchemaHandler
{
    /**
     * @var SamedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var SamedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @var SamedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayPackageRepository
     */
    private SamedayPackageRepository $samedayPackageRepository;

    /**
     * @var SamedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var SamedayCityRepository
     */
    private SamedayCityRepository $samedayCityRepository;

    /**
     * @var DbHandlerInterface
     */
    private DbHandlerInterface $dbHandler;

    public function __construct()
    {
        $this->dbHandler = new DbHandler();
        $this->samedayAwbRepository = new SamedayAwbRepository();
        $this->samedayPickupPointRepository = new SamedayPickupPointRepository();
        $this->samedayServiceRepository = new SamedayServiceRepository();
        $this->samedayPackageRepository = new SamedayPackageRepository();
        $this->samedayLockerRepository = new SamedayLockerRepository();
        $this->samedayCityRepository = new SamedayCityRepository();
    }

    /**
     * @return string
     */
    public function buildAwbTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . $this->samedayAwbRepository->getTableName() . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            order_id INT(11) NOT NULL,
            awb_number VARCHAR(255),
            parcels TEXT,
            awb_cost DOUBLE(10, 2),
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) " . $this->dbHandler->getCharsetCollate() . ";";
    }

    /**
     * @return string
     */
    public function buildPickUpPointTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . $this->samedayPickupPointRepository->getTableName() . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            sameday_id INT(11) NOT NULL,
            sameday_alias VARCHAR(255),
            is_testing TINYINT(1),
            city VARCHAR(255),
            county VARCHAR(255),
            address VARCHAR(255),
            contactPersons TEXT,
            default_pickup_point TINYINT(1),
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
            ) " . $this->dbHandler->getCharsetCollate() . ";";
    }

    /**
     * @return string
     */
    public function buildServiceTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . $this->samedayServiceRepository->getTableName() . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            sameday_id INT(11) NOT NULL,
            sameday_name VARCHAR(255),
            is_testing TINYINT(1),
            name VARCHAR(255),
            price DOUBLE(10, 2),
            price_free DOUBLE(10, 2),
            status INT(11),
            sameday_code VARCHAR(255) NOT NULL DEFAULT '',
            service_optional_taxes TEXT DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) " . $this->dbHandler->getCharsetCollate() . ";";
    }

    /**
     * @return string
     */
    public function buildPackageTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . $this->samedayPackageRepository->getTableName() . " (
            order_id INT(11) NOT NULL,
            awb_parcel VARCHAR(255),
            summary TEXT,
            history TEXT,
            expedition_status TEXT,
            sync TEXT,
            PRIMARY KEY (order_id, awb_parcel)
        ) " . $this->dbHandler->getCharsetCollate() . ";";
    }

    /**
     * @return string
     */
    public function buildLockerTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . $this->samedayLockerRepository->getTableName() . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            locker_id INT(11),
            name VARCHAR(255),
            county VARCHAR(255),
            city VARCHAR(255),
            address VARCHAR(255),
            lat VARCHAR(255),
            lng VARCHAR(255),
            postal_code VARCHAR(255),
            boxes TEXT,
            is_testing TINYINT(1),
            PRIMARY KEY (id)
        ) " . $this->dbHandler->getCharsetCollate() . ";";
    }

    public function buildCitiesTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . $this->samedayCityRepository->getTableName() . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            city_id INT(11),
            city_name VARCHAR(255),
            county_code VARCHAR(255),
            postal_code VARCHAR(10),
            country_code VARCHAR(10),
            PRIMARY KEY (id)
        ) " . $this->dbHandler->getCharsetCollate() . ";";
    }

    /**
     * @return void
     */
    public function createTables(): void
    {
        $tablesToCreate = [
            $this->buildAwbTableQuery(),
            $this->buildPickUpPointTableQuery(),
            $this->buildServiceTableQuery(),
            $this->buildPackageTableQuery(),
            $this->buildLockerTableQuery(),
            $this->buildCitiesTableQuery(),
        ];

        foreach ($tablesToCreate as $query) {
            $this->createTable($query);
        }
    }

    /**
     * @param string $createTableQuery
     *
     * @return void
     */
    public function createTable(string $createTableQuery): void
    {
        $this->dbHandler->executeQuery($createTableQuery);
    }

    /**
     * @return void
     */
    public function alterTables(): void
    {
        $service = $this->samedayServiceRepository->getTableName();

        $tablesToAlter = [];
        if (!$this->dbHandler->columnExists($service, 'sameday_code')) {
            $tablesToAlter[] = "ALTER TABLE $service ADD `sameday_code` VARCHAR(255) NOT NULL DEFAULT '';";
        }

        if (!$this->dbHandler->columnExists($service, 'service_optional_taxes')) {
            $tablesToAlter[] = "ALTER TABLE $service ADD `service_optional_taxes` TEXT DEFAULT NULL ;";
        }

        foreach ($tablesToAlter as $sql) {
            $this->dbHandler->executeQuery($sql);
        }
    }
}
