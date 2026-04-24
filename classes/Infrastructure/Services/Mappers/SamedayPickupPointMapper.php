<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\Models\SamedayPickupPoint;

final class SamedayPickupPointMapper extends AbstractMapper
{
    /**
     * @param array $row
     *
     * @return SamedayPickupPoint
     */
    public function map(array $row): SamedayPickupPoint
    {
        $pickupPoint = new SamedayPickupPoint();

        $pickupPoint->setId((int) $row["id"]);
        $pickupPoint->setSamedayId((int) $row["sameday_id"]);
        $pickupPoint->setSamedayAlias($row["sameday_alias"] ?? null);
        $pickupPoint->setIsTesting(isset($row["is_testing"]) ? ((int) $row["is_testing"] !== 0) : null);
        $pickupPoint->setCity($row["city"] ?? null);
        $pickupPoint->setCounty($row["county"] ?? null);
        $pickupPoint->setAddress($row["address"] ?? null);
        $pickupPoint->setContactPersons($row["contactPersons"] ?? null);
        $pickupPoint->setDefaultPickupPoint(
            isset($row["default_pickup_point"]) ? ((int) $row["default_pickup_point"] !== 0) : null
        );

        return $pickupPoint;
    }
}
