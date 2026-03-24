<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use Sameday\Objects\PickupPoint\PickupPointObject;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\AbstractRepository;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

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
     * @return array
     */
    public function getPickupPointSameday(int $samedayId): array
    {
        $queryString = $this->dbHandler->prepareQuery(
            "SELECT * FROM %s WHERE sameday_id = %d AND is_testing = %s LIMIT 1",
            [
                $this->getTableName(),
                $samedayId,
                Helper::isTesting(),
            ]
        );

        return $this->dbHandler->getRow($queryString);
    }

    /**
     * @return array
     */
    public function getPickupPoints(): array
    {
        $queryString = $this->dbHandler->prepareQuery(
            "SELECT * FROM %s WHERE is_testing = %s",
            [
                $this->getTableName(),
                Helper::isTesting(),
            ]
        );

        return $this->dbHandler->getRows($queryString);
    }

    /**
     * @return int|null
     */
    public function getDefaultPickupPointId(): ?int
    {
        $queryString = $this->dbHandler->prepareQuery(
            "SELECT sameday_id FROM %s WHERE default_pickup_point = 1 AND is_testing = %s LIMIT 1",
            [
                $this->getTableName(),
                Helper::isTesting(),
            ]
        );
        $result = $this->dbHandler->getRow($queryString);

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
    public function updatePickupPoint(PickupPointObject $pickupPointObject, int $id): void
    {
        $this->dbHandler->updateRow(
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
