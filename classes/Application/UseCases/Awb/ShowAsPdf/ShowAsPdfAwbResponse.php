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
     * @param string $noticeMessage
     * @param bool $hasError
     * @param int $orderId
     * @param string|null $pdf
     */
    public function __construct(
        string $noticeMessage,
        bool $hasError,
        int $orderId,
        ?string $pdf = null
    ) {
        $this->noticeMessage = $noticeMessage;
        $this->hasError = $hasError;
        $this->orderId = $orderId;
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
