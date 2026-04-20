<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

use SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday\SamedayPackageRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

class SchemaHandler
{
    
    /**
     * @return string
     */
    public static function buildAwbTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . SamedayAwbRepository::getTableName() . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            order_id INT(11) NOT NULL,
            awb_number VARCHAR(255),
            parcels TEXT,
            awb_cost DOUBLE(10, 2),
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) " . DbHandler::getCharsetCollate() . ";";
    }

    public static function buildPickUpPointTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . SamedayPickupPointRepository::getTableName() . " (
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
            ) " . DbHandler::getCharsetCollate() . ";";
    }

    public static function buildServiceTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . SamedayServiceRepository::getTableName() . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            sameday_id INT(11) NOT NULL,
            sameday_name VARCHAR(255),
            is_testing TINYINT(1),
            name VARCHAR(255),
            price DOUBLE(10, 2),
            price_free DOUBLE(10, 2),
            status INT(11),
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) " . DbHandler::getCharsetCollate() . ";";
    }

    public static function buildPackageTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . SamedayPackageRepository::getTableName() . " (
            order_id INT(11) NOT NULL,
            awb_parcel VARCHAR(255),
            summary TEXT,
            history TEXT,
            expedition_status TEXT,
            sync TEXT,
            PRIMARY KEY (order_id, awb_parcel)
        ) " . DbHandler::getCharsetCollate() . ";";
    }

    public static function buildLockerTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . SamedayLockerRepository::getTableName() . " (
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
        ) " . DbHandler::getCharsetCollate() . ";";
    }

    public static function buildCitiesTableQuery(): string
    {
        return "CREATE TABLE IF NOT EXISTS " . SamedayCityRepository::getTableName() . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            city_id INT(11),
            city_name VARCHAR(255),
            county_code VARCHAR(255),
            postal_code VARCHAR(10),
            country_code VARCHAR(10),
            PRIMARY KEY (id)
        ) " . DbHandler::getCharsetCollate() . ";";
    }

    /**
     * @param string $createTableQuery
     *
     * @return void
     */
    public static function createTable(string $createTableQuery): void
    {
        DbHandler::executeQuery($createTableQuery);
    }

    /**
     * @return void
     */
    public static function createTables(): void
    {
        $tablesToCreate = [
            self::buildAwbTableQuery(),
            self::buildPickUpPointTableQuery(),
            self::buildServiceTableQuery(),
            self::buildPackageTableQuery(),
            self::buildLockerTableQuery(),
            self::buildCitiesTableQuery(),
        ];

        foreach ($tablesToCreate as $query) {
            self::createTable($query);
        }
    }

    /**
     * @return void
     */
    public static function alterTables(): void
    {
        $service = SamedayServiceRepository::getTableName();
        $servicesRows = DbHandler::getRow("SELECT * FROM $service LIMIT 1");

        $tablesToAlter = [];
        if (!isset($servicesRows->sameday_code)) {
            $alterServiceTable = "ALTER TABLE $service ADD `sameday_code` VARCHAR(255) NOT NULL DEFAULT '';";

            $tablesToAlter[] = $alterServiceTable;
        }

        if (!isset($servicesRows->service_optional_taxes)) {
            $alterServiceTable = "ALTER TABLE $service ADD `service_optional_taxes` TEXT DEFAULT NULL ;";

            $tablesToAlter[] = $alterServiceTable;
        }

        if (!empty($tablesToAlter)) {
            foreach ($tablesToAlter as $sql) {
                DbHandler::executeQuery($sql);
            }
        }
    }
}
