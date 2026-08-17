<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class CourierPickupPointDto
{
    private int $id;

    private string $alias;

    private string $cityName;

    private string $countyName;

    private string $address;

    private bool $isDefault;

    private ?string $serializedContactPersons;

    public function __construct(
        int $id,
        string $alias,
        string $cityName,
        string $countyName,
        string $address,
        bool $isDefault,
        ?string $serializedContactPersons = null
    ) {
        $this->id = $id;
        $this->alias = $alias;
        $this->cityName = $cityName;
        $this->countyName = $countyName;
        $this->address = $address;
        $this->isDefault = $isDefault;
        $this->serializedContactPersons = $serializedContactPersons;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function getCountyName(): string
    {
        return $this->countyName;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getSerializedContactPersons(): ?string
    {
        return $this->serializedContactPersons;
    }
}
