<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class AddNewPickupPointServiceRequestDto
{
    private string $pickupPointCountryId;

    private string $pickupPointCountyId;

    private string $pickupPointCityId;

    private string $pickupPointAddress;

    private string $pickupPointPostalCode;

    private string $pickupPointAlias;

    private string $pickupPointContactPersonName;

    private string $pickupPointContactPersonPhone;

    private bool $isDefault;

    public function __construct(
        string $pickupPointCountryId,
        string $pickupPointCountyId,
        string $pickupPointCityId,
        string $pickupPointAddress,
        string $pickupPointPostalCode,
        string $pickupPointAlias,
        string $pickupPointContactPersonName,
        string $pickupPointContactPersonPhone,
        bool $isDefault
    ) {
        $this->pickupPointCountryId = $pickupPointCountryId;
        $this->pickupPointCountyId = $pickupPointCountyId;
        $this->pickupPointCityId = $pickupPointCityId;
        $this->pickupPointAddress = $pickupPointAddress;
        $this->pickupPointPostalCode = $pickupPointPostalCode;
        $this->pickupPointAlias = $pickupPointAlias;
        $this->pickupPointContactPersonName = $pickupPointContactPersonName;
        $this->pickupPointContactPersonPhone = $pickupPointContactPersonPhone;
        $this->isDefault = $isDefault;
    }

    public function getPickupPointCountryId(): string
    {
        return $this->pickupPointCountryId;
    }

    public function getPickupPointCountyId(): string
    {
        return $this->pickupPointCountyId;
    }

    public function getPickupPointCityId(): string
    {
        return $this->pickupPointCityId;
    }

    public function getPickupPointAddress(): string
    {
        return $this->pickupPointAddress;
    }

    public function getPickupPointPostalCode(): string
    {
        return $this->pickupPointPostalCode;
    }

    public function getPickupPointAlias(): string
    {
        return $this->pickupPointAlias;
    }

    public function getPickupPointContactPersonName(): string
    {
        return $this->pickupPointContactPersonName;
    }

    public function getPickupPointContactPersonPhone(): string
    {
        return $this->pickupPointContactPersonPhone;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }
}
