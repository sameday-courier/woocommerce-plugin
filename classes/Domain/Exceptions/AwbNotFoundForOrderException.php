<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Exceptions;

use RuntimeException;

if (!defined('ABSPATH')) {
    exit;
}

final class AwbNotFoundForOrderException extends RuntimeException
{
    private int $orderId;

    /**
     * @param int $orderId
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
        parent::__construct(sprintf('AWB not found for order %d', $orderId));
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
