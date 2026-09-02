<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class ShowHistoryAwbResponse implements ResponseInterface
{
    use NoticerTrait;

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
     * @param string $noticeMessage
     * @param bool $hasError
     */
    public function __construct(
        int $orderId,
        bool $hasAwb,
        array $packages,
        string $noticeMessage = '',
        bool $hasError = false
    ) {
        $this->orderId = $orderId;
        $this->hasAwb = $hasAwb;
        $this->packages = $packages;
        $this->noticeMessage = $noticeMessage;
        $this->hasError = $hasError;
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
