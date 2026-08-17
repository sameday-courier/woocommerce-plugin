<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

final class StartBulkGenerateAwbItem implements ItemInterface
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
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        $orderIds = $inputParams['samedaycourier-order-ids'] ?? [];
        if (!is_array($orderIds)) {
            $orderIds = [];
        }

        $orderIds = array_values(
            array_filter(
                array_map(
                    static function ($orderId): int {
                        return (int) $orderId;
                    },
                    $orderIds
                ),
                static function (int $orderId): bool {
                    return $orderId > 0;
                }
            )
        );

        return new self(
            $orderIds,
            (int) ($inputParams['samedaycourier-user-id'] ?? 0)
        );
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
