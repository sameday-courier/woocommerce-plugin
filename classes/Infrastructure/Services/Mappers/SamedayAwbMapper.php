<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use SamedayCourier\Shipping\Domain\Models\CarrierAwb;

/**
 * @extends AbstractMapper<CarrierAwb>
 */
final class SamedayAwbMapper extends AbstractMapper
{
    /**
     * @param array $row
     *
     * @return CarrierAwb
     */
    public function map(array $row): CarrierAwb
    {
        $awb = new CarrierAwb();

        $awb->setId((int) $row["id"]);
        $awb->setOrderId((int) $row["order_id"]);
        $awb->setAwbNumber($row["awb_number"] ?? null);
        $awb->setParcels($row["parcels"] ?? null);
        $awb->setAwbCost(isset($row['awb_cost']) ? (float) $row['awb_cost'] : null);

        return $awb;
    }
}
