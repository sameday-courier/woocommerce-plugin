<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class GetCitiesServiceResponseDto
{
    private bool $success;

    private string $message;

    /**
     * @var array<int, array{id: int, name: string}>
     */
    private array $cities;

    /**
     * @param array<int, array{id: int, name: string}> $cities
     */
    public function __construct(bool $success, string $message, array $cities = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->cities = $cities;
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
    public function getCities(): array
    {
        return $this->cities;
    }
}
