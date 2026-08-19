<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

use SamedayCourier\Shipping\Domain\Models\CarrierAwb;

final class PostRemoveAwbRequestDto
{
    private CarrierAwb $awb;

    /**
     * @param CarrierAwb $awb
     */
    public function __construct(CarrierAwb $awb)
    {
        $this->awb = $awb;
    }

    /**
     * @return CarrierAwb
     */
    public function getAwb(): CarrierAwb
    {
        return $this->awb;
    }
}
