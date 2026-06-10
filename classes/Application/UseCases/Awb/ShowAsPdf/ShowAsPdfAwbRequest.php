<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

if (!defined('ABSPATH')) {
    exit;
}

final class ShowAsPdfAwbRequest
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var string $labelFormat
     */
    private string $labelFormat;

    /**
     * @param int $orderId
     * @param string $labelFormat
     */
    public function __construct(int $orderId, string $labelFormat)
    {
        $this->orderId = $orderId;
        $this->labelFormat = $labelFormat;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getLabelFormat(): string
    {
        return $this->labelFormat;
    }
}
