<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use Sameday\Objects\ParcelStatusHistory\ExpeditionObject;
use Sameday\Objects\ParcelStatusHistory\SummaryObject;
use SamedayCourier\Shipping\Domain\Models\SamedayPackage;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayPackageMapper;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\AbstractRepository;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayPackageRepository extends AbstractRepository
{
    private const TABLE_NAME = 'sameday_package';

    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
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
    public function refreshPackageHistory(
        int $orderId,
        string $awbParcel,
        SummaryObject $summary,
        array $history,
        ExpeditionObject $expedition
    ): void
    {
        $this->dbHandler->insertRow(
            $this->getTableName(),
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
     * @return SamedayPackage[]
     */
    public function getPackagesForOrderId(int $orderId): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM %s WHERE order_id = %d",
            [
                $this->getTableName(),
                $orderId,
            ]
        );

        return $this->getMapper(SamedayPackageMapper::class)->mapCollection($rows);
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function deletePackagesByOrderId(int $orderId): void
    {
        $this->dbHandler->deleteRow(
            $this->getTableName(),
            [
                'order_id' => $orderId,
            ]
        );
    }
}
