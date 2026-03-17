<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayPackageRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

class Installer
{
    /**
     * @return void
     */
    public static function run(): void
    {
        $charset_collate = DbHandler::getCharsetCollate();

        $awbTable =  SamedayAwbRepository::getTableName();
        $pickup_point = SamedayPickupPointRepository::getTableName();
        $service = SamedayServiceRepository::getTableName();
        $packageTable = SamedayPackageRepository::getTableName();
        $lockerTable = SamedayLockerRepository::getTableName();
        $citiesTable = SamedayCityRepository::getTableName();

        $createAwbTable = "CREATE TABLE IF NOT EXISTS $awbTable (
		id INT(11) NOT NULL AUTO_INCREMENT,
        order_id INT(11) NOT NULL,
        awb_number VARCHAR(255),
        parcels TEXT,
        awb_cost DOUBLE(10, 2),
        PRIMARY KEY (id),
		UNIQUE KEY id (id)
	) $charset_collate;";

        $createPickUpPointTable = "CREATE TABLE IF NOT EXISTS $pickup_point (
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
	) $charset_collate;";

        $createServiceTable = "CREATE TABLE IF NOT EXISTS $service (
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
	) $charset_collate;";

        $createPackageTable = "CREATE TABLE IF NOT EXISTS $packageTable (
		order_id INT(11) NOT NULL,
        awb_parcel VARCHAR(255),
        summary TEXT,
        history TEXT,
        expedition_status TEXT,
        sync TEXT,
        PRIMARY KEY (order_id, awb_parcel)
	) $charset_collate;";

        $createLockerTable = "CREATE TABLE IF NOT EXISTS $lockerTable (
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
	) $charset_collate;";

        $createCitiesTable = "CREATE TABLE IF NOT EXISTS $citiesTable (
        id INT(11) NOT NULL AUTO_INCREMENT,
        city_id INT(11),
        city_name VARCHAR(255),
        county_code VARCHAR(255),
        postal_code VARCHAR(10),
        country_code VARCHAR(10),
        PRIMARY KEY (id)
    ) $charset_collate;";

        $tablesToCreate = [
            $createAwbTable,
            $createPickUpPointTable,
            $createServiceTable,
            $createPackageTable,
            $createLockerTable,
            $createCitiesTable
        ];

        $tablesToAlter = [];

        $servicesRows = DbHandler::getRow("SELECT * FROM $service LIMIT 1");

        if (!isset($servicesRows->sameday_code)) {
            $alterServiceTable = "ALTER TABLE $service ADD `sameday_code` VARCHAR(255) NOT NULL DEFAULT '';";

            $tablesToAlter[] = $alterServiceTable;
        }

        if (!isset($servicesRows->service_optional_taxes)) {
            $alterServiceTable = "ALTER TABLE $service ADD `service_optional_taxes` TEXT DEFAULT NULL ;";

            $tablesToAlter[] = $alterServiceTable;
        }

        /**
         * Create Tables
         */
        foreach ($tablesToCreate as $sql) {
            DbHandler::executeQuery($sql);
        }

        if (! empty($tablesToAlter)) {
            foreach ($tablesToAlter as $sql) {
                DbHandler::executeQuery($sql);
            }
        }
    }
}
