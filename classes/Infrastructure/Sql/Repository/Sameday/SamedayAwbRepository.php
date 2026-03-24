<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Infrastructure\Sql\Repository\AbstractRepository;

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
     * @return array
     */
    public function getAwbForOrderId(int $orderId): array
    {
        $queryString = $this->dbHandler->prepareQuery(
            "SELECT * FROM %s WHERE order_id = %d LIMIT 1",
            [
                $this->getTableName(),
                $orderId,
            ]
        );

        return $this->dbHandler->getRow($queryString);
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
     * @param array $awb Must contain 'id' and 'order_id' keys
     *
     * @return void
     */
    public function deleteAwbAndParcels(array $awb): void
    {
        $id = $awb['id'] ?? null;
        $orderId = $awb['order_id'] ?? null;
        $samedayPackageRepository = new SamedayPackageRepository($this->dbHandler);

        if ($id !== null) {
            $this->dbHandler->deleteRow($this->getTableName(), ['id' => $id]);
        }
        if ($orderId !== null) {
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
