<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

use SamedayCourier\Shipping\Domain\DTOs\CourierLockerDto;

final class GetLockersResponseDto
{
    /**
     * @var CourierLockerDto[]
     */
    private array $lockers;

    private int $pages;

    /**
     * @param CourierLockerDto[] $lockers
     */
    public function __construct(array $lockers, int $pages)
    {
        $this->lockers = $lockers;
        $this->pages = $pages;
    }

    /**
     * @return CourierLockerDto[]
     */
    public function getLockers(): array
    {
        return $this->lockers;
    }

    public function getPages(): int
    {
        return $this->pages;
    }
}
