<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use SamedayCourier\Shipping\Domain\Models\SamedayPackage;

final class SamedayPackageMapper extends AbstractMapper
{
    /**
     * @param array $row
     *
     * @return SamedayPackage
     */
    public function map(array $row): SamedayPackage
    {
        $package = new SamedayPackage();

        $package->setOrderId((int) $row["order_id"]);
        $package->setAwbParcel($row["awb_parcel"] ?? null);
        $package->setSummary($row["summary"] ?? null);
        $package->setHistory($row["history"] ?? null);
        $package->setExpeditionStatus($row["expedition_status"] ?? null);
        $package->setSync($row["sync"] ?? null);

        return $package;
    }
}
