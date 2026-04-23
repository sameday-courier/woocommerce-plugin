<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use SamedayCourier\Shipping\Domain\ModelInterface;
use SamedayCourier\Shipping\Domain\Models\SamedayService;

final class SamedayServiceMapper implements MapperInterface
{
    /**
     * @param array $row Column keys and scalar values as returned for the service table (e.g. from wpdb ARRAY_A).
     *
     * @return ModelInterface
     */
    public function map(array $row): ModelInterface
    {
        $service = new SamedayService();

        $service->setId((int) $row['id']);
        $service->setSamedayId((int) $row['sameday_id']);
        $service->setSamedayCode((string) $row['sameday_code']);

        $samedayName = $row['sameday_name'] ?? null;
        $service->setSamedayName($samedayName === null ? null : (string) $samedayName);

        $isTesting = $row['is_testing'] ?? null;
        $service->setIsTesting($isTesting === null ? null : ((int) $isTesting !== 0));

        $name = $row['name'] ?? null;
        $service->setName($name === null ? null : (string) $name);

        $price = $row['price'] ?? null;
        $service->setPrice($price === null ? null : (float) $price);

        $priceFree = $row['price_free'] ?? null;
        $service->setPriceFree($priceFree === null ? null : (float) $priceFree);

        $status = $row['status'] ?? null;
        $service->setStatus($status === null ? null : (int) $status);

        $serviceOptionalTaxes = $row['service_optional_taxes'] ?? null;
        $service->setServiceOptionalTaxes(
            $serviceOptionalTaxes === null ? null : (string) $serviceOptionalTaxes
        );

        return $service;
    }

    /**
     * @param array $rows
     *
     * @return ModelInterface[]
     */
    public function mapCollection(array $rows): array
    {
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->map($row);
        }

        return $collection;
    }
}
