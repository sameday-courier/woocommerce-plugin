<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class CourierLoginResponseDto
{
    private bool $successful;

    /**
     * @param bool $successful
     */
    public function __construct(bool $successful)
    {
        $this->successful = $successful;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->successful;
    }
}
