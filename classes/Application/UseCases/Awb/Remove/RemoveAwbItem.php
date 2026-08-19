<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

final class RemoveAwbItem implements ItemInterface
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @param int $orderId
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @param array $inputParams
     *
     * @return self
     */
    /**
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        $orderId = (int) ($inputParams['order-id'] ?? null);

        return new self(
            $orderId,
        );
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
