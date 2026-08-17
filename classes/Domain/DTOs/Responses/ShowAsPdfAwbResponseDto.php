<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class ShowAsPdfAwbResponseDto
{
    private int $orderId;

    private bool $success;

    private string $message;

    private ?string $pdf;

    public function __construct(
        int $orderId,
        bool $success,
        string $message,
        ?string $pdf = null
    ) {
        $this->orderId = $orderId;
        $this->success = $success;
        $this->message = $message;
        $this->pdf = $pdf;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }
}
