<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class StartBulkRemoveAwbRequestDto
{
    /**
     * @var int[] $orderIds
     */
    private array $orderIds;

    private int $userId;

    /**
     * @param int[] $orderIds
     * @param int $userId
     */
    public function __construct(array $orderIds, int $userId)
    {
        $this->orderIds = $orderIds;
        $this->userId = $userId;
    }

    /**
     * @return int[]
     */
    public function getOrderIds(): array
    {
        return $this->orderIds;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
