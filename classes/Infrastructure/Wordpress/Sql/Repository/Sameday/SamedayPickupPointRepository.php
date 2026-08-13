<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday;

use Sameday\Objects\PickupPoint\PickupPointObject;
use SamedayCourier\Shipping\Domain\Models\SamedayPickupPoint;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayPickupPointMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\AbstractRepository;
use SamedayCourier\Shipping\Domain\SamedaySettings;
class SamedayPickupPointRepository extends AbstractRepository
{
    private const TABLE_NAME = 'sameday_pickup_point';

    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @param int $samedayId
     *
     * @return SamedayPickupPoint|null
     */
    public function getPickupPointSameday(int $samedayId): ?SamedayPickupPoint
    {
        $row = $this->dbHandler->getRow(
            "SELECT * FROM {$this->getTableName()} WHERE sameday_id = %d AND is_testing = %s LIMIT 1",
            [
                $samedayId,
                SamedaySettings::isTesting(),
            ]
        );

        if ($row === []) {
            return null;
        }

        return $this->getMapper(SamedayPickupPointMapper::class)->map($row);
    }

    /**
     * @return SamedayPickupPoint[]
     */
    public function getPickupPoints(): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM {$this->getTableName()} WHERE is_testing = %s",
            [
                SamedaySettings::isTesting(),
            ]
        );

        return $this->getMapper(SamedayPickupPointMapper::class)->mapCollection($rows);
    }

    /**
     * @return int|null
     */
    public function getDefaultPickupPointId(): ?int
    {
        $result = $this->dbHandler->getRow(
            "SELECT sameday_id FROM {$this->getTableName()} WHERE default_pickup_point = 1 AND is_testing = %s LIMIT 1",
            [
                SamedaySettings::isTesting(),
            ]
        );

        return isset($result['sameday_id']) ? (int) $result['sameday_id'] : null;
    }

    /**
     * @param PickupPointObject $pickupPointObject
     *
     * @return void
     */
    public function addPickupPoint(PickupPointObject $pickupPointObject): void
    {
        $this->dbHandler->insertRow(
            $this->getTableName(),
            [
                'sameday_id' => $pickupPointObject->getId(),
                'sameday_alias' => $pickupPointObject->getAlias(),
                'is_testing' => SamedaySettings::isTesting(),
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
     * @return bool
     */
    public function updatePickupPoint(PickupPointObject $pickupPointObject, int $id): bool
    {
        return $this->dbHandler->updateRow(
            $this->getTableName(),
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
    public function deletePickupPoint(int $id): void
    {
        $this->dbHandler->deleteRow($this->getTableName(), ['id' => $id]);
    }
}
