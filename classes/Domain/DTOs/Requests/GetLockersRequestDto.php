<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class GetLockersRequestDto
{
    /**
     * @var int[]
     */
    private array $lockerIds;

    private int $page;

    /**
     * @param int[] $lockerIds
     */
    public function __construct(array $lockerIds = [], int $page = 1)
    {
        $this->lockerIds = $lockerIds;
        $this->page = $page;
    }

    /**
     * @return int[]
     */
    public function getLockerIds(): array
    {
        return $this->lockerIds;
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
