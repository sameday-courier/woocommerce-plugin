<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

use Sameday\Objects\PickupPoint\PickupPointContactPersonObject;

final class PostPickupPointRequestDto
{
    private $countryId;

    private $countyId;

    private $cityId;

    private string $address;

    private string $postalCode;

    private string $alias;

    /**
     * @var PickupPointContactPersonObject[]
     */
    private array $contactPersons;

    private bool $defaultPickupPoint;

    /**
     * @param mixed $countryId
     * @param mixed $countyId
     * @param mixed $cityId
     * @param PickupPointContactPersonObject[] $contactPersons
     */
    public function __construct(
        $countryId,
        $countyId,
        $cityId,
        string $address,
        string $postalCode,
        string $alias,
        array $contactPersons,
        bool $defaultPickupPoint
    ) {
        $this->countryId = $countryId;
        $this->countyId = $countyId;
        $this->cityId = $cityId;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->alias = $alias;
        $this->contactPersons = $contactPersons;
        $this->defaultPickupPoint = $defaultPickupPoint;
    }

    /**
     * @return mixed
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * @return mixed
     */
    public function getCountyId()
    {
        return $this->countyId;
    }

    /**
     * @return mixed
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return PickupPointContactPersonObject[]
     */
    public function getContactPersons(): array
    {
        return $this->contactPersons;
    }

    public function isDefaultPickupPoint(): bool
    {
        return $this->defaultPickupPoint;
    }
}
