<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Sql\Repository\Sameday;

if (!defined( 'ABSPATH')) {
    exit;
}

use Sameday\Objects\Locker\LockerObject;
use SamedayCourier\Shipping\Domain\Models\SamedayLocker;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayLockerMapper;
use SamedayCourier\Shipping\Application\Sql\Repository\AbstractRepository;
use SamedayCourier\Shipping\Domain\SamedaySettings;
class SamedayLockerRepository extends AbstractRepository
{
    private const TABLE_NAME = 'sameday_locker';

    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @return SamedayLocker[]
     */
    public function getCitiesWithLockers(): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT city, county FROM {$this->getTableName()} WHERE is_testing = %s GROUP BY city",
            [
                SamedaySettings::isTesting()
            ]
        );

        return $this->getMapper(SamedayLockerMapper::class)->mapCollection($rows);
    }

    /**
     * @return SamedayLocker[]
     */
    public function getLockers(): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM {$this->getTableName()} WHERE is_testing = %s",
            [
                SamedaySettings::isTesting(),
            ]
        );

        return $this->getMapper(SamedayLockerMapper::class)->mapCollection($rows);
    }

    /**
     * @param string $city
     *
     * @return SamedayLocker[]
     */
    public function getLockersByCity(string $city): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM {$this->getTableName()} WHERE city = %s AND is_testing = %s",
            [
                $city,
                SamedaySettings::isTesting()
            ]
        );

        return $this->getMapper(SamedayLockerMapper::class)->mapCollection($rows);
    }

    /**
     * @param int $samedayId
     *
     * @return SamedayLocker|null
     */
    public function getLockerSameday(int $samedayId): ?SamedayLocker
    {
        $row = $this->dbHandler->getRow(
            "SELECT * FROM {$this->getTableName()} WHERE locker_id = %d AND is_testing = %s LIMIT 1",
            [
                $samedayId,
                SamedaySettings::isTesting()
            ]
        );

        if ($row === []) {
            return null;
        }

        return $this->getMapper(SamedayLockerMapper::class)->map($row);
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
                'is_testing' => SamedaySettings::isTesting(),
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
