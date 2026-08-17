<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Domain\DTOs\CourierPickupPointDto;
use SamedayCourier\Shipping\Domain\Models\CarrierPickupPoint;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayPickupPointMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\DbHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\AbstractRepository;

class SamedayPickupPointRepository extends AbstractRepository
{
    private const TABLE_NAME = 'sameday_pickup_point';

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
     * @param int $samedayId
     *
     * @return CarrierPickupPoint|null
     */
    public function getPickupPointSameday(int $samedayId): ?CarrierPickupPoint
    {
        $row = $this->dbHandler->getRow(
            "SELECT * FROM {$this->getTableName()} WHERE sameday_id = %d AND is_testing = %s LIMIT 1",
            [
                $samedayId,
                $this->isTesting(),
            ]
        );

        if ($row === []) {
            return null;
        }

        return $this->getMapper(SamedayPickupPointMapper::class)->map($row);
    }

    /**
     * @return CarrierPickupPoint[]
     */
    public function getPickupPoints(): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM {$this->getTableName()} WHERE is_testing = %s",
            [
                $this->isTesting(),
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
                $this->isTesting(),
            ]
        );

        return isset($result['sameday_id']) ? (int) $result['sameday_id'] : null;
    }

    /**
     * @param CourierPickupPointDto $pickupPoint
     *
     * @return void
     */
    public function addPickupPoint(CourierPickupPointDto $pickupPoint): void
    {
        $this->dbHandler->insertRow(
            $this->getTableName(),
            [
                'sameday_id' => $pickupPoint->getId(),
                'sameday_alias' => $pickupPoint->getAlias(),
                'is_testing' => $this->isTesting(),
                'city' => $pickupPoint->getCityName(),
                'county' => $pickupPoint->getCountyName(),
                'address' => $pickupPoint->getAddress(),
                'default_pickup_point' => $pickupPoint->isDefault(),
                'contactPersons' => $pickupPoint->getSerializedContactPersons(),
            ]
        );
    }

    /**
     * @param CourierPickupPointDto $pickupPoint
     * @param int $id
     *
     * @return bool
     */
    public function updatePickupPoint(CourierPickupPointDto $pickupPoint, int $id): bool
    {
        return $this->dbHandler->updateRow(
            $this->getTableName(),
            [
                'sameday_alias' => $pickupPoint->getAlias(),
                'city' => $pickupPoint->getCityName(),
                'county' => $pickupPoint->getCountyName(),
                'address' => $pickupPoint->getAddress(),
                'default_pickup_point' => $pickupPoint->isDefault(),
                'contactPersons' => $pickupPoint->getSerializedContactPersons(),
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

    /**
     * @return bool
     */
    private function isTesting(): bool
    {
        return $this->carrierSettingsProvider->get()->isTesting();
    }
}
