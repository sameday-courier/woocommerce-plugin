<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class LockerDtoRequest
{
    private int $lockerId;

    /**
     * @param int $lockerId
     */
    public function __construct(int $lockerId)
    {
        $this->lockerId = $lockerId;
    }

    /**
     * @return int
     */
    public function getLockerId(): int
    {
        return $this->lockerId;
    }
}
