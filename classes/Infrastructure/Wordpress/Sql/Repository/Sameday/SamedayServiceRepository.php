<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday;

use Sameday\Objects\Service\OptionalTaxObject;
use SamedayCourier\Shipping\Domain\DTOs\CarrierOptionalTaxDto;
use SamedayCourier\Shipping\Domain\DTOs\CourierServiceDto;
use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CarrierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayOptionalTaxMapper;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayServiceMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\DbHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\SerializedPayloadReader;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\AbstractRepository;

class SamedayServiceRepository extends AbstractRepository implements CarrierServiceProviderInterface
{
    private const TABLE_NAME = 'sameday_service';

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

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @return CarrierService[]
     */
    public function getAvailableServices(): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM {$this->getTableName()} WHERE is_testing = %s AND status > 0",
            [
                $this->isTesting(),
            ]
        );

        return $this->getMapper(SamedayServiceMapper::class)->mapCollection($rows);
    }

    /**
     * @return CarrierService[]
     */
    public function getServices(): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT * FROM {$this->getTableName()} WHERE is_testing = %s",
            [
                $this->isTesting(),
            ]
        );

        return $this->getMapper(SamedayServiceMapper::class)->mapCollection($rows);
    }

    /**
     * @param int $samedayServiceId
     *
     * @return CarrierOptionalTaxDto[]
     */
    public function getServiceIdOptionalTaxes(int $samedayServiceId): array
    {
        $rows = $this->dbHandler->getRows(
            "SELECT service_optional_taxes FROM {$this->getTableName()}"
            . " WHERE is_testing = %s AND sameday_id = %d LIMIT 1",
            [
                $this->isTesting(),
                $samedayServiceId,
            ]
        );
        if (empty($rows) || empty($rows[0]['service_optional_taxes'])) {
            return [];
        }

        $result = SerializedPayloadReader::readOptionalTaxes($rows[0]['service_optional_taxes']);
        $optionalTaxes = array_values(
            array_filter(
                $result,
                static function ($optionalTax): bool {
                    return $optionalTax instanceof OptionalTaxObject;
                }
            )
        );

        return (new SamedayOptionalTaxMapper())->mapCollection($optionalTaxes);
    }

    /**
     * @param int $id
     *
     * @return CarrierService|null
     */
    public function getServiceById(int $id): ?CarrierService
    {
        $row = $this->dbHandler->getRow(
            "SELECT * FROM {$this->getTableName()} WHERE id = %d LIMIT 1",
            [
                $id,
            ]
        );

        if ($row === []) {
            return null;
        }

        return $this->getMapper(SamedayServiceMapper::class)->map($row);
    }

    /**
     * @param int $samedayId
     *
     * @return CarrierService|null
     */
    public function getServiceSameday(int $samedayId): ?CarrierService
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

        return $this->getMapper(SamedayServiceMapper::class)->map($row);
    }

    /**
     * @param string $samedayCode
     *
     * @return CarrierService|null
     */
    public function getServiceSamedayByCode(string $samedayCode): ?CarrierService
    {
        $row = $this->dbHandler->getRow(
            "SELECT * FROM {$this->getTableName()} WHERE sameday_code = %s AND is_testing = %s LIMIT 1",
            [
                $samedayCode,
                $this->isTesting(),
            ]
        );

        if ($row === []) {
            return null;
        }

        return $this->getMapper(SamedayServiceMapper::class)->map($row);
    }

    /**
     * @param CourierServiceDto $service
     *
     * @return void
     */
    public function addService(CourierServiceDto $service): void
    {
        $this->dbHandler->insertRow(
            $this->getTableName(),
            [
                'sameday_id' => $service->getId(),
                'sameday_name' => $service->getName(),
                'sameday_code' => $service->getCode(),
                'is_testing' => $this->isTesting(),
                'status' => 0,
                'service_optional_taxes' => $service->getSerializedOptionalTaxes(),
            ]
        );
    }

    /**
     * @param array $service
     *
     * @return bool
     */
    public function updateService(array $service): bool
    {
        $id = $service['id'];
        unset($service['id']);

        return $this->dbHandler->updateRow(
            $this->getTableName(),
            $service,
            [
                'id' => $id,
            ]
        );
    }

    /**
     * @param CourierServiceDto $service
     * @param int $id
     *
     * @return bool
     */
    public function updateServiceCode(CourierServiceDto $service, int $id): bool
    {
        $serviceName = $service->getName();
        if ($service->getCode() === CarrierConstants::LOCKER_NEXT_DAY_CODE) {
            $hostCountry = $this->carrierSettingsProvider->get()->getHostCountry();
            $serviceName = CarrierConstants::OOH_SERVICES_LABELS[$hostCountry];
        }

        return $this->dbHandler->updateRow(
            $this->getTableName(),
            [
                'sameday_code' => $service->getCode(),
                'name' => $serviceName,
                'service_optional_taxes' => $service->getSerializedOptionalTaxes(),
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
    public function deleteService(int $id): void
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
