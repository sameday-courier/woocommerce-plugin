<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

use SamedayCourier\Shipping\Domain\DTOs\LockerDto;

final class LockerDtoResponse
{
    private ?LockerDto $locker;

    /**
     * @param ?LockerDto $locker
     */
    public function __construct(?LockerDto $locker = null)
    {
        $this->locker = $locker;
    }

    /**
     * @return ?LockerDto
     */
    public function getLocker(): ?LockerDto
    {
        return $this->locker;
    }
}
