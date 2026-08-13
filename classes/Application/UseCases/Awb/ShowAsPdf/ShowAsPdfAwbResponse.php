<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class ShowAsPdfAwbResponse implements ResponseInterface
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
     * @param string|null $noticeMessage
     * @param string $noticeType
     * @param string|null $pdf
     */
    public function __construct(
        int $orderId,
        ?string $noticeMessage,
        string $noticeType,
        ?string $pdf = null
    ) {
        $this->orderId = $orderId;
        $this->noticeMessage = $noticeMessage;
        $this->noticeType = $noticeType;
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
