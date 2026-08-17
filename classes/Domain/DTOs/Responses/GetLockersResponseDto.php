<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

use Sameday\Objects\Locker\LockerObject;

final class GetLockersResponseDto
{
    /**
     * @var LockerObject[]
     */
    private array $lockers;

    private int $pages;

    /**
     * @param LockerObject[] $lockers
     */
    public function __construct(array $lockers, int $pages)
    {
        $this->lockers = $lockers;
        $this->pages = $pages;
    }

    /**
     * @return LockerObject[]
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
