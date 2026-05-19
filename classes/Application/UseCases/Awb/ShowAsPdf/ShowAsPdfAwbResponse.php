<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

if (!defined('ABSPATH')) {
    exit;
}

class ShowAsPdfAwbResponse
{
    use NoticerTrait;

    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var string|null $pdf
     */
    private ?string $pdf;

    /**
     * @param int $orderId
     * @param string $noticeType
     * @param string|null $noticeMessage
     * @param string|null $pdf
     */
    public function __construct(
        int $orderId,
        string $noticeType,
        ?string $noticeMessage = null,
        ?string $pdf = null
    ) {
        $this->orderId = $orderId;
        $this->noticeType = $noticeType;
        $this->noticeMessage = $noticeMessage;
        $this->pdf = $pdf;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return string|null
     */
    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    /**
     * @return bool
     */
    public function hasPdf(): bool
    {
        return null !== $this->pdf && '' !== $this->pdf;
    }
}
