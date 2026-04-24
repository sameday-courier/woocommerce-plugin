<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\Models\SamedayAwb;

final class SamedayAwbMapper extends AbstractMapper
{
    /**
     * @param array $row
     *
     * @return SamedayAwb
     */
    public function map(array $row): SamedayAwb
    {
        $awb = new SamedayAwb();

        $awb->setId((int) $row["id"]);
        $awb->setOrderId((int) $row["order_id"]);
        $awb->setAwbNumber($row["awb_number"] ?? null);
        $awb->setParcels($row["parcels"] ?? null);
        $awb->setAwbCost($row["awb_cost"] ?? null);

        return $awb;
    }
}
