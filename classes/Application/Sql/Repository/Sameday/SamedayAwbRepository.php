<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Domain\Models\SamedayAwb;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayAwbMapper;
use SamedayCourier\Shipping\Application\Sql\Repository\AbstractRepository;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayAwbRepository extends AbstractRepository
{
    private const TABLE_NAME = 'sameday_awb';

    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @param int $orderId
     *
     * @return SamedayAwb|null
     */
    public function getAwbForOrderId(int $orderId): ?SamedayAwb
    {
        $row = $this->dbHandler->getRow(
            "SELECT * FROM %s WHERE order_id = %d LIMIT 1",
            [
                $this->getTableName(),
                $orderId,
            ]
        );

        if ($row === []) {
            return null;
        }

        return $this->getMapper(SamedayAwbMapper::class)->map($row);
    }

    /**
     * @param array $awb
     *
     * @return void
     */
    public function saveAwb(array $awb): void
    {
        $this->dbHandler->insertRow($this->getTableName(), $awb);
    }

    /**
     * @param SamedayAwb $awb
     *
     * @return void
     */
    public function deleteAwbAndParcels(SamedayAwb $awb): void
    {
        $id = $awb->getId();
        $orderId = $awb->getOrderId();
        $samedayPackageRepository = new SamedayPackageRepository($this->dbHandler);

        if ($id > 0) {
            $this->dbHandler->deleteRow($this->getTableName(), ['id' => $id]);
        }
        if ($orderId > 0) {
            $samedayPackageRepository->deletePackagesByOrderId($orderId);
        }
    }

    /**
     * @param int $orderId
     * @param string $parcels Serialized parcels data
     *
     * @return void
     */
    public function updateParcels(int $orderId, string $parcels): void
    {
        $this->dbHandler->updateRow(
            $this->getTableName(),
            [
                'parcels' => $parcels
            ],
            [
                'order_id' => $orderId
            ]
        );
    }
}
