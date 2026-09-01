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

    /**
     * @param int $id
     * @param string $alias
     * @param string $cityName
     * @param string $countyName
     * @param string $address
     * @param bool $isDefault
     * @param ?string $serializedContactPersons
     */
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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
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
    public function getCountyName(): string
    {
        return $this->countyName;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @return ?string
     */
    public function getSerializedContactPersons(): ?string
    {
        return $this->serializedContactPersons;
    }
}
