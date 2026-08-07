<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Sql\Repository\Sameday;

if (!defined( 'ABSPATH')) {
    exit;
}

use RuntimeException;
use SamedayCourier\Shipping\Domain\Models\SamedayAwb;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayAwbMapper;
use SamedayCourier\Shipping\Application\Sql\Repository\AbstractRepository;

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
            "SELECT * FROM {$this->getTableName()} WHERE order_id = %d LIMIT 1",
            [
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
     *
     * @throws RuntimeException When the AWB row could not be persisted.
     */
    public function saveAwb(array $awb): void
    {
        if (!$this->dbHandler->insertRow($this->getTableName(), $awb)) {
            throw new RuntimeException('Unable to persist the generated AWB.');
        }
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
     * @return bool
     */
    public function updateParcels(int $orderId, string $parcels): bool
    {
        return $this->dbHandler->updateRow(
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
