<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Infrastructure\Sql\Repository\RepositoryInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayAwbRepository implements RepositoryInterface
{
    private const TABLE_NAME = 'sameday_awb';

    public static function getTableName(): string
    {
        return DbHandler::buildTableName(self::TABLE_NAME);
    }

    /**
     * @param int $orderId
     *
     * @return array
     */
    public static function getAwbForOrderId(int $orderId): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE order_id = %d LIMIT 1",
            [
                self::getTableName(),
                $orderId,
            ]
        );

        return DbHandler::getRow($queryString);
    }

    /**
     * @param array $awb
     *
     * @return void
     */
    public static function saveAwb(array $awb): void
    {
        DbHandler::insertRow(self::getTableName(), $awb);
    }

    /**
     * @param array $awb Must contain 'id' and 'order_id' keys
     *
     * @return void
     */
    public static function deleteAwbAndParcels(array $awb): void
    {
        $id = $awb['id'] ?? null;
        $orderId = $awb['order_id'] ?? null;

        if ($id !== null) {
            DbHandler::deleteRow(self::getTableName(), ['id' => $id]);
        }
        if ($orderId !== null) {
            SamedayPackageRepository::deletePackagesByOrderId($orderId);
        }
    }

    /**
     * @param int $orderId
     * @param string $parcels Serialized parcels data
     *
     * @return void
     */
    public static function updateParcels(int $orderId, string $parcels): void
    {
        DbHandler::updateRow(
            self::getTableName(),
            [
                'parcels' => $parcels
            ],
            [
                'order_id' => $orderId
            ]
        );
    }
}
