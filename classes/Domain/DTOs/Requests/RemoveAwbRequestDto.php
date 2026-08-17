<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class RemoveAwbRequestDto
{
    private string $awb;

    /**
     * @param string $awb
     */
    public function __construct(string $awb)
    {
        $this->awb = $awb;
    }

    /**
     * @return string
     */
    public function getAwb(): string
    {
        return $this->awb;
    }
}
