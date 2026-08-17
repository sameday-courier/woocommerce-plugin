<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class GetCountiesServiceResponseDto
{
    private bool $success;

    private string $message;

    /**
     * @var array<int, array{id: int, name: string}>
     */
    private array $counties;

    /**
     * @param array<int, array{id: int, name: string}> $counties
     */
    public function __construct(bool $success, string $message, array $counties = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->counties = $counties;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getCounties(): array
    {
        return $this->counties;
    }
}
