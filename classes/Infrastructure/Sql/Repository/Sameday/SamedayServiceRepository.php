<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use Sameday\Objects\Service\OptionalTaxObject;
use Sameday\Objects\Service\ServiceObject;
use Sameday\Objects\Types\CostType;
use Sameday\Objects\Types\PackageType;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\RepositoryInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayServiceRepository implements RepositoryInterface
{
    private const TABLE_NAME = 'sameday_service';

    public static function getTableName(): string
    {
        return DbHandler::buildTableName(self::TABLE_NAME);
    }

    /**
     * @return array
     */
    public static function getAvailableServices(): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE is_testing = %s AND status > 0",
            [
                self::getTableName(),
                Helper::isTesting(),
            ]
        );

        return DbHandler::getRows($queryString);
    }

    /**
     * @return array
     */
    public static function getServices(): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE is_testing = %s",
            [
                self::getTableName(),
                Helper::isTesting(),
            ]
        );

        return DbHandler::getRows($queryString);
    }

    /**
     * @param int $samedayServiceId
     *
     * @return OptionalTaxObject[]
     */
    public static function getServiceIdOptionalTaxes(int $samedayServiceId): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT service_optional_taxes FROM %s WHERE is_testing = %s AND sameday_id = %d LIMIT 1",
            [
                self::getTableName(),
                Helper::isTesting(),
                $samedayServiceId,
            ]
        );

        $rows = DbHandler::getRows($queryString);
        if (empty($rows) || empty($rows[0]['service_optional_taxes'])) {
            return [];
        }

        /** @var OptionalTaxObject[]|false $result */
        $result = unserialize(
            $rows[0]['service_optional_taxes'],
            [
                'allowed_classes' => [
                    OptionalTaxObject::class,
                    PackageType::class, CostType::class
                ],
            ]
        );

        return is_array($result) ? $result : [];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public static function getService(int $id): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE id = %d LIMIT 1",
            [
                self::getTableName(),
                $id,
            ]
        );

        return DbHandler::getRow($queryString);
    }

    /**
     * @param int $samedayId
     *
     * @return array
     */
    public static function getServiceSameday(int $samedayId): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE sameday_id = %d AND is_testing = %s LIMIT 1",
            [
                self::getTableName(),
                $samedayId,
                Helper::isTesting(),
            ]
        );

        return DbHandler::getRow($queryString);
    }

    /**
     * @param string $samedayCode
     *
     * @return array
     */
    public static function getServiceSamedayByCode(string $samedayCode): array
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT * FROM %s WHERE sameday_code = %s AND is_testing = %s LIMIT 1",
            [
                self::getTableName(),
                $samedayCode,
                Helper::isTesting(),
            ]
        );

        return DbHandler::getRow($queryString);
    }

    /**
     * @param ServiceObject $service
     *
     * @return void
     */
    public static function addService(ServiceObject $service): void
    {
        $optionalTaxes = $service->getOptionalTaxes();

        DbHandler::insertRow(
            self::getTableName(),
            [
                'sameday_id' => $service->getId(),
                'sameday_name' => $service->getName(),
                'sameday_code' => $service->getCode(),
                'is_testing' => Helper::isTesting(),
                'status' => 0,
                'service_optional_taxes' => !empty($optionalTaxes) ? serialize($optionalTaxes) : null,
            ]
        );
    }

    /**
     * @param array $service
     *
     * @return void
     */
    public static function updateService(array $service): void
    {
        $id = $service['id'];
        unset($service['id']);

        DbHandler::updateRow(
            self::getTableName(),
            $service,
            [
                'id' => $id,
            ]
        );
    }

    /**
     * @param ServiceObject $serviceObject
     * @param int $id
     *
     * @return void
     */
    public static function updateServiceCode(ServiceObject $serviceObject, int $id): void
    {
        $serviceName = $serviceObject->getName();
        if ($serviceObject->getCode() === Helper::LOCKER_NEXT_DAY_CODE) {
            $serviceName = Helper::OOH_SERVICES_LABELS[Helper::getHostCountry()];
        }

        DbHandler::updateRow(
            self::getTableName(),
            [
                'sameday_code' => $serviceObject->getCode(),
                'name' => $serviceName,
                'service_optional_taxes' => !empty($serviceObject->getOptionalTaxes())
                    ? serialize($serviceObject->getOptionalTaxes())
                    : null,
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
    public static function deleteService(int $id): void
    {
        DbHandler::deleteRow(self::getTableName(), ['id' => $id]);
    }
}
