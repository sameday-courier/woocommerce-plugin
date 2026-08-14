<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use Sameday\Objects\CityObject;

final class GetCitiesResponseDto
{
    /**
     * @var CityObject[]
     */
    private array $cities;

    private int $pages;

    /**
     * @param CityObject[] $cities
     */
    public function __construct(array $cities, int $pages)
    {
        $this->cities = $cities;
        $this->pages = $pages;
    }

    /**
     * @return CityObject[]
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
