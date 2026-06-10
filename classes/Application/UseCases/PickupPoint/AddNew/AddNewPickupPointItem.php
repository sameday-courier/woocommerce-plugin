<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

if (!defined('ABSPATH')) {
    exit;
}

final class AddNewPickupPointItem
{
    /**
     * @var string $pickupPointCountryId
     */
    private string $pickupPointCountryId;

    /**
     * @var string $pickupPointCountyId
     */
    private string $pickupPointCountyId;

    /**
     * @var string $pickupPointCityId
     */
    private string $pickupPointCityId;

    /**
     * @var string $pickupPointAddress
     */
    private string $pickupPointAddress;

    /**
     * @var string $pickupPointPostalCode
     */
    private string $pickupPointPostalCode;

    /**
     * @var string $pickupPointAlias
     */
    private string $pickupPointAlias;

    /**
     * @var string $pickupPointContactPersonName
     */
    private string $pickupPointContactPersonName;

    /**
     * @var string $pickupPointContactPersonPhone
     */
    private string $pickupPointContactPersonPhone;

    /**
     * @var bool $isDefault
     */
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
    )
    {
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

    /**
     * @return string
     */
    public function getPickupPointCountryId(): string
    {
        return $this->pickupPointCountryId;
    }

    /**
     * @return string
     */
    public function getPickupPointCountyId(): string
    {
        return $this->pickupPointCountyId;
    }

    /**
     * @return string
     */
    public function getPickupPointCityId(): string
    {
        return $this->pickupPointCityId;
    }

    /**
     * @return string
     */
    public function getPickupPointAddress(): string
    {
        return $this->pickupPointAddress;
    }

    /**
     * @return string
     */
    public function getPickupPointPostalCode(): string
    {
        return $this->pickupPointPostalCode;
    }

    /**
     * @return string
     */
    public function getPickupPointAlias(): string
    {
        return $this->pickupPointAlias;
    }

    /**
     * @return string
     */
    public function getPickupPointContactPersonName(): string
    {
        return $this->pickupPointContactPersonName;
    }

    /**
     * @return string
     */
    public function getPickupPointContactPersonPhone(): string
    {
        return $this->pickupPointContactPersonPhone;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }
}
