<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class CourierLockerDto
{
    private int $id;

    private string $name;

    private string $city;

    private string $county;

    private string $address;

    private string $lat;

    private string $lng;

    private string $postalCode;

    private ?string $serializedBoxes;

    public function __construct(
        int $id,
        string $name,
        string $city,
        string $county,
        string $address,
        string $lat,
        string $lng,
        string $postalCode,
        ?string $serializedBoxes = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->city = $city;
        $this->county = $county;
        $this->address = $address;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->postalCode = $postalCode;
        $this->serializedBoxes = $serializedBoxes;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCounty(): string
    {
        return $this->county;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getLat(): string
    {
        return $this->lat;
    }

    public function getLng(): string
    {
        return $this->lng;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getSerializedBoxes(): ?string
    {
        return $this->serializedBoxes;
    }
}
