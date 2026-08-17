<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Domain\Models\CarrierPackage;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayPackageMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\AbstractRepository;
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
     * @param mixed $summary
     * @param array $history
     * @param mixed $expedition
     *
     * @return void
     */
    public function refreshPackageHistory(
        int $orderId,
        string $awbParcel,
        $summary,
        array $history,
        $expedition
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
     * @return CarrierPackage[]
     */
    public function getPackagesForOrderId(int $orderId): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM {$this->getTableName()} WHERE order_id = %d",
            [
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
