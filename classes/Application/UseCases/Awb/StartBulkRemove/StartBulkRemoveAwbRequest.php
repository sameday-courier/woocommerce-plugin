<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove;

use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

final class StartBulkRemoveAwbRequest implements RequestInterface
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

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}
