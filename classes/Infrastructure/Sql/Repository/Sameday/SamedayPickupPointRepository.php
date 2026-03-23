<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use Sameday\Objects\PickupPoint\PickupPointObject;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\RepositoryInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayPickupPointRepository implements RepositoryInterface
{
    private const TABLE_NAME = 'sameday_pickup_point';

    public static function getTableName(): string
    {
        return DbHandler::buildTableName(self::TABLE_NAME);
    }

    /**
     * @param int $samedayId
     *
     * @return array
     */
    public static function getPickupPointSameday(int $samedayId): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE sameday_id = %d AND is_testing = %s LIMIT 1",
            [
                self::getTableName(),
                $samedayId,
                Helper::isTesting(),
            ]
        );

        return DbHandler::getRow($queryString);
    }

    /**
     * @return array
     */
    public static function getPickupPoints(): array
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
     * @return int|null
     */
    public static function getDefaultPickupPointId(): ?int
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT sameday_id FROM %s WHERE default_pickup_point = 1 AND is_testing = %s LIMIT 1",
            [
                self::getTableName(),
                Helper::isTesting(),
            ]
        );
        $result = DbHandler::getRow($queryString);

        return isset($result['sameday_id']) ? (int) $result['sameday_id'] : null;
    }

    /**
     * @param PickupPointObject $pickupPointObject
     *
     * @return void
     */
    public static function addPickupPoint(PickupPointObject $pickupPointObject): void
    {
        DbHandler::insertRow(
            self::getTableName(),
            [
                'sameday_id' => $pickupPointObject->getId(),
                'sameday_alias' => $pickupPointObject->getAlias(),
                'is_testing' => Helper::isTesting(),
                'city' => $pickupPointObject->getCity()->getName(),
                'county' => $pickupPointObject->getCounty()->getName(),
                'address' => $pickupPointObject->getAddress(),
                'default_pickup_point' => $pickupPointObject->isDefault(),
                'contactPersons' => serialize($pickupPointObject->getContactPersons()),
            ]
        );
    }

    /**
     * @param PickupPointObject $pickupPointObject
     * @param int $id
     *
     * @return void
     */
    public static function updatePickupPoint(PickupPointObject $pickupPointObject, int $id): void
    {
        DbHandler::updateRow(
            self::getTableName(),
            [
                'sameday_alias' => $pickupPointObject->getAlias(),
                'city' => $pickupPointObject->getCity()->getName(),
                'county' => $pickupPointObject->getCounty()->getName(),
                'address' => $pickupPointObject->getAddress(),
                'default_pickup_point' => $pickupPointObject->isDefault(),
                'contactPersons' => serialize($pickupPointObject->getContactPersons()),
            ],
            [
                'id' => $id,
            ]
        );
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public static function deletePickupPoint(int $id): void
    {
        DbHandler::deleteRow(self::getTableName(), ['id' => $id]);
    }
}
