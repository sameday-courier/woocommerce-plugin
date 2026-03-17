<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository;

use Sameday\Objects\Locker\LockerObject;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayLockerRepository implements RepositoryInterface
{
    private const TABLE_NAME = 'sameday_locker';

    public static function getTableName(): string
    {
        return DbHandler::buildTableName(self::TABLE_NAME);
    }

    /**
     * @return array
     */
    public static function getCitiesWithLockers(): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT city, county FROM %s WHERE is_testing = %s GROUP BY city",
            [
                self::getTableName(),
                Helper::isTesting()
            ]
        );

        return DbHandler::getRows($queryString);
    }

    /**
     * @return array
     */
    public static function getLockers(): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE is_testing = %s",
            [
                self::getTableName(),
                Helper::isTesting(),
            ]
        );

        return DbHandler::getRows($queryString);
    }

    /**
     * @param string $city
     *
     * @return array
     */
    public static function getLockersByCity(string $city): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE city = %s AND is_testing = %s",
            [
                self::getTableName(),
                $city,
                Helper::isTesting()
            ]
        );

        return DbHandler::getRows($queryString);
    }

    /**
     * @param int $samedayId
     *
     * @return array
     */
    public static function getLockerSameday(int $samedayId): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE locker_id = %d AND is_testing = %s LIMIT 1",
            [
                self::getTableName(),
                $samedayId,
                Helper::isTesting()
            ]
        );

        return DbHandler::getRow($queryString);
    }

    public static function addLocker(LockerObject $lockerObject): void
    {
        DbHandler::insertRow(
            self::getTableName(),
            [
                'locker_id' => $lockerObject->getId(),
                'name' => $lockerObject->getName(),
                'city' => $lockerObject->getCity(),
                'county' => $lockerObject->getCounty(),
                'address' => $lockerObject->getAddress(),
                'lat' => $lockerObject->getLat(),
                'lng' => $lockerObject->getLong(),
                'postal_code' => $lockerObject->getPostalCode(),
                'boxes' => serialize($lockerObject->getBoxes()),
                'is_testing' => Helper::isTesting(),
            ]
        );
    }

    public static function updateLocker(LockerObject $lockerObject, int $id): void
    {
        DbHandler::updateRow(
            self::getTableName(),
            [
                'locker_id' => $lockerObject->getId(),
                'name' => $lockerObject->getName(),
                'city' => $lockerObject->getCity(),
                'county' => $lockerObject->getCounty(),
                'address' => $lockerObject->getAddress(),
                'lat' => $lockerObject->getLat(),
                'lng' => $lockerObject->getLong(),
                'postal_code' => $lockerObject->getPostalCode(),
                'boxes' => serialize($lockerObject->getBoxes()),
            ],
            [
                'id' => $id
            ]
        );
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public static function deleteLocker(int $id): void
    {
        DbHandler::deleteRow(self::getTableName(), ['id' => $id]);
    }
}
