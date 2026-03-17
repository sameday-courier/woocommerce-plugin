<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository;

use Sameday\Objects\ParcelStatusHistory\ExpeditionObject;
use Sameday\Objects\ParcelStatusHistory\SummaryObject;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayPackageRepository implements RepositoryInterface
{
    private const TABLE_NAME = 'sameday_package';

    public static function getTableName(): string
    {
        return DbHandler::buildTableName(self::TABLE_NAME);
    }

    /**
     * @param int $orderId
     * @param string $awbParcel
     * @param SummaryObject $summary
     * @param array $history
     * @param ExpeditionObject $expedition
     *
     * @return void
     */
    public static function refreshPackageHistory(
        int $orderId,
        string $awbParcel,
        SummaryObject $summary,
        array $history,
        ExpeditionObject $expedition
    ): void {
        DbHandler::insertRow(
            self::getTableName(),
            [
                'order_id' => $orderId,
                'awb_parcel' => $awbParcel,
                'summary' => serialize($summary),
                'history' => serialize($history),
                'expedition_status' => serialize($expedition),
                'sync' => null,
            ]
        );
    }

    /**
     * @param int $orderId
     *
     * @return array
     */
    public static function getPackagesForOrderId(int $orderId): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE order_id = %d",
            [
                self::getTableName(),
                $orderId,
            ]
        );

        return DbHandler::getRows($queryString);
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    public static function deletePackagesByOrderId(int $orderId): void
    {
        DbHandler::deleteRow(
            self::getTableName(),
            [
                'order_id' => $orderId,
            ]
        );
    }
}
