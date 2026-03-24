<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use Sameday\Objects\Locker\LockerObject;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\AbstractRepository;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayLockerRepository extends AbstractRepository
{
    private const TABLE_NAME = 'sameday_locker';

    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @return array
     */
    public function getCitiesWithLockers(): array
    {
        $queryString = $this->dbHandler->prepareQuery(
            "SELECT city, county FROM %s WHERE is_testing = %s GROUP BY city",
            [
                $this->getTableName(),
                Helper::isTesting()
            ]
        );

        return $this->dbHandler->getRows($queryString);
    }

    /**
     * @return array
     */
    public function getLockers(): array
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
     * @param string $city
     *
     * @return array
     */
    public function getLockersByCity(string $city): array
    {
        $queryString = $this->dbHandler->prepareQuery(
            "SELECT * FROM %s WHERE city = %s AND is_testing = %s",
            [
                $this->getTableName(),
                $city,
                Helper::isTesting()
            ]
        );

        return $this->dbHandler->getRows($queryString);
    }

    /**
     * @param int $samedayId
     *
     * @return array
     */
    public function getLockerSameday(int $samedayId): array
    {
        $queryString = $this->dbHandler->prepareQuery(
            "SELECT * FROM %s WHERE locker_id = %d AND is_testing = %s LIMIT 1",
            [
                $this->getTableName(),
                $samedayId,
                Helper::isTesting()
            ]
        );

        return $this->dbHandler->getRow($queryString);
    }

    public function addLocker(LockerObject $lockerObject): void
    {
        $this->dbHandler->insertRow(
            $this->getTableName(),
            [
                'locker_id' => $lockerObject->getId(),
                'name' => $lockerObject->getName(),
                'city' => $lockerObject->getCity(),
                'county' => $lockerObject->getCounty(),
                'address' => $lockerObject->getAddress(),
                'lat' => $lockerObject->getLat(),
                'lng' => $lockerObject->getLong(),
                'postal_code' => $lockerObject->getPostalCode(),
                'boxes' => serialize($lockerObject->getBoxes()),
                'is_testing' => Helper::isTesting(),
            ]
        );
    }

    /**
     * @param LockerObject $lockerObject
     * @param int $id
     *
     * @return void
     */
    public function updateLocker(LockerObject $lockerObject, int $id): void
    {
        $this->dbHandler->updateRow(
            $this->getTableName(),
            [
                'locker_id' => $lockerObject->getId(),
                'name' => $lockerObject->getName(),
                'city' => $lockerObject->getCity(),
                'county' => $lockerObject->getCounty(),
                'address' => $lockerObject->getAddress(),
                'lat' => $lockerObject->getLat(),
                'lng' => $lockerObject->getLong(),
                'postal_code' => $lockerObject->getPostalCode(),
                'boxes' => serialize($lockerObject->getBoxes()),
            ],
            [
                'id' => $id
            ]
        );
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteLocker(int $id): void
    {
        $this->dbHandler->deleteRow($this->getTableName(), ['id' => $id]);
    }
}
