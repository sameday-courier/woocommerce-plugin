<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class CitySourceDto
{
    private int $cityId;

    private string $cityName;

    private string $countyCode;

    private string $postalCode;

    private string $countryCode;

    public function __construct(
        int $cityId,
        string $cityName,
        string $countyCode,
        string $postalCode,
        string $countryCode
    ) {
        $this->cityId = $cityId;
        $this->cityName = $cityName;
        $this->countyCode = $countyCode;
        $this->postalCode = $postalCode;
        $this->countryCode = $countryCode;
    }

    public function getCityId(): int
    {
        return $this->cityId;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function getCountyCode(): string
    {
        return $this->countyCode;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }
}
