<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class GetCitiesResponseDto
{
    /**
     * @var array<int, array{id: int, name: string}>
     */
    private array $cities;

    private int $pages;

    /**
     * @param array<int, array{id: int, name: string}> $cities
     */
    public function __construct(array $cities, int $pages)
    {
        $this->cities = $cities;
        $this->pages = $pages;
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getCities(): array
    {
        return $this->cities;
    }

    public function getPages(): int
    {
        return $this->pages;
    }
}
