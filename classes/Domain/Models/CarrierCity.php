<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Models;

final class CarrierCity implements ModelInterface
{
    public int $id;
    public ?int $cityId;
    public ?string $cityName;
    public ?string $countyCode;
    public ?string $postalCode;
    public ?string $countryCode;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * 
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCityId(): ?int
    {
        return $this->cityId;
    }

    /**
     * @param int|null $cityId
     * 
     * @return self
     */
    public function setCityId(?int $cityId): self
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    /**
     * @param string|null $cityName
     * 
     * @return self
     */
    public function setCityName(?string $cityName): self
    {
        $this->cityName = $cityName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountyCode(): ?string
    {
        return $this->countyCode;
    }

    /**
     * @param string|null $countyCode
     * 
     * @return self
     */
    public function setCountyCode(?string $countyCode): self
    {
        $this->countyCode = $countyCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @param string|null $postalCode
     * 
     * @return self
     */
    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @param string|null $countryCode
     * 
     * @return self
     */
    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return array{city_name: ?string, county_code: ?string}
     */
    public function toArray(): array
    {
        return [
            'city_name' => $this->cityName,
            'county_code' => $this->countyCode,
        ];
    }
}
