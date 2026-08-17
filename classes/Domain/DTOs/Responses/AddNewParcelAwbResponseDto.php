<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class AddNewParcelAwbResponseDto
{
    private int $orderId;

    private bool $success;

    private string $message;

    public function __construct(int $orderId, bool $success, string $message)
    {
        $this->orderId = $orderId;
        $this->success = $success;
        $this->message = $message;
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
}
