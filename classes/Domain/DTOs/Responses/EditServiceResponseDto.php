<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class EditServiceResponseDto
{
    private int $serviceId;

    private bool $success;

    private string $message;

    public function __construct(int $serviceId, bool $success, string $message)
    {
        $this->serviceId = $serviceId;
        $this->success = $success;
        $this->message = $message;
    }

    public function getServiceId(): int
    {
        return $this->serviceId;
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
