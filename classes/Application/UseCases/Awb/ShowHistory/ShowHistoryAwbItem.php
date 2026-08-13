<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

final class ShowHistoryAwbItem implements ItemInterface
{
    private int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        return new self(
            (int) ($inputParams['order-id'] ?? 0),
        );
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
