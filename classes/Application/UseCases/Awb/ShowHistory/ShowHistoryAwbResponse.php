<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

final class ShowHistoryAwbResponse
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var bool $hasAwb
     */
    private bool $hasAwb;

    /**
     * @var array $packages
     */
    private array $packages;

    /**
     * @param int $orderId
     * @param bool $hasAwb
     * @param array $packages
     */
    public function __construct(
        int $orderId,
        bool $hasAwb,
        array $packages
    ) {
        $this->orderId = $orderId;
        $this->hasAwb = $hasAwb;
        $this->packages = $packages;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return bool
     */
    public function hasAwb(): bool
    {
        return $this->hasAwb;
    }

    /**
     * @return array
     */
    public function getPackages(): array
    {
        return $this->packages;
    }
}
