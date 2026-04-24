<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\Models\SamedayService;

final class SamedayServiceMapper extends AbstractMapper
{
    /**
     * @param array $row
     *
     * @return SamedayService
     */
    public function map(array $row): SamedayService
    {
        $service = new SamedayService();

        $service->setId((int) $row["id"]);
        $service->setSamedayId((int) $row["sameday_id"]);
        $service->setSamedayCode((string) $row["sameday_code"]);
        $service->setSamedayName($row["sameday_name"] ?? null);
        $service->setIsTesting((bool) ($row["is_testing"] ?? false));
        $service->setName($row["name"] ?? null);
        $service->setPrice(isset($row['price']) && $row['price'] !== '' ? (float) $row['price'] : null);
        $service->setPriceFree(
            isset($row['price_free']) && $row['price_free'] !== '' ? (float) $row['price_free'] : null
        );
        $service->setStatus($row["status"] ?? null);
        $service->setServiceOptionalTaxes($row["service_optional_taxes"] ?? null);

        return $service;
    }
}
