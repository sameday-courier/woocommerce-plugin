<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class RemoveAwbResponseDto
{
    private string $message;

    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
