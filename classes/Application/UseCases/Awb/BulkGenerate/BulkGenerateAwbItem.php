<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\BulkGenerate;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class BulkGenerateAwbItem implements ItemInterface
{
    /**
     * @var int[] $orderIds
     */
    private array $orderIds;

    /**
     * @param int[] $orderIds
     */
    public function __construct(array $orderIds)
    {
        $this->orderIds = $orderIds;
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

        return new self($orderIds);
    }

    /**
     * @return int[]
     */
    public function getOrderIds(): array
    {
        return $this->orderIds;
    }
}
