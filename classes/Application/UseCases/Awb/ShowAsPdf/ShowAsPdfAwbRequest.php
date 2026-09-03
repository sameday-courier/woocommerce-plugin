<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

final class ShowAsPdfAwbRequest implements RequestInterface
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
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
