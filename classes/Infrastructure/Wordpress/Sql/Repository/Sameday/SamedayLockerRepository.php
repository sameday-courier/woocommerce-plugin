<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Domain\DTOs\CourierLockerDto;
use SamedayCourier\Shipping\Domain\Models\CarrierLocker;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayLockerMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\DbHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\AbstractRepository;

class SamedayLockerRepository extends AbstractRepository
{
    private const TABLE_NAME = 'sameday_locker';

    /**
     * @var CarrierSettingsProviderInterface
     */
    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    /**
     * @param DbHandlerInterface|null $dbHandler
     * @param CarrierSettingsProviderInterface|null $carrierSettingsProvider
     */
    public function __construct(
        ?DbHandlerInterface $dbHandler = null,
        ?CarrierSettingsProviderInterface $carrierSettingsProvider = null
    ) {
        parent::__construct($dbHandler);
        $this->carrierSettingsProvider = $carrierSettingsProvider ?? new CarrierSettingsServiceProvider();
    }

    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @return CarrierLocker[]
     */
    public function getCitiesWithLockers(): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT city, county FROM {$this->getTableName()} WHERE is_testing = %s GROUP BY city",
            [
                $this->isTesting()
            ]
        );

        return $this->getMapper(SamedayLockerMapper::class)->mapCollection($rows);
    }

    /**
     * @return CarrierLocker[]
     */
    public function getLockers(): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM {$this->getTableName()} WHERE is_testing = %s",
            [
                $this->isTesting(),
            ]
        );

        return $this->getMapper(SamedayLockerMapper::class)->mapCollection($rows);
    }

    /**
     * @param string $city
     *
     * @return CarrierLocker[]
     */
    public function getLockersByCity(string $city): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM {$this->getTableName()} WHERE city = %s AND is_testing = %s",
            [
                $city,
                $this->isTesting()
            ]
        );

        return $this->getMapper(SamedayLockerMapper::class)->mapCollection($rows);
    }

    /**
     * @param int $samedayId
     *
     * @return CarrierLocker|null
     */
    public function getLockerSameday(int $samedayId): ?CarrierLocker
    {
        $row = $this->dbHandler->getRow(
            "SELECT * FROM {$this->getTableName()} WHERE locker_id = %d AND is_testing = %s LIMIT 1",
            [
                $samedayId,
                $this->isTesting()
            ]
        );

        if ($row === []) {
            return null;
        }

        return $this->getMapper(SamedayLockerMapper::class)->map($row);
    }

    public function addLocker(CourierLockerDto $locker): void
    {
        $this->dbHandler->insertRow(
            $this->getTableName(),
            [
                'locker_id' => $locker->getId(),
                'name' => $locker->getName(),
                'city' => $locker->getCity(),
                'county' => $locker->getCounty(),
                'address' => $locker->getAddress(),
                'lat' => $locker->getLat(),
                'lng' => $locker->getLng(),
                'postal_code' => $locker->getPostalCode(),
                'boxes' => $locker->getSerializedBoxes(),
                'is_testing' => $this->isTesting(),
            ]
        );
    }

    /**
     * @param CourierLockerDto $locker
     * @param int $id
     *
     * @return bool
     */
    public function updateLocker(CourierLockerDto $locker, int $id): bool
    {
        return $this->dbHandler->updateRow(
            $this->getTableName(),
            [
                'locker_id' => $locker->getId(),
                'name' => $locker->getName(),
                'city' => $locker->getCity(),
                'county' => $locker->getCounty(),
                'address' => $locker->getAddress(),
                'lat' => $locker->getLat(),
                'lng' => $locker->getLng(),
                'postal_code' => $locker->getPostalCode(),
                'boxes' => $locker->getSerializedBoxes(),
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

    /**
     * @return bool
     */
    private function isTesting(): bool
    {
        return $this->carrierSettingsProvider->get()->isTesting();
    }
}
