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

    /**
     * @param int $cityId
     * @param string $cityName
     * @param string $countyCode
     * @param string $postalCode
     * @param string $countryCode
     */
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

    /**
     * @return int
     */
    public function getCityId(): int
    {
        return $this->cityId;
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->cityName;
    }

    /**
     * @return string
     */
    public function getCountyCode(): string
    {
        return $this->countyCode;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }
}
