<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class LockerDto
{
    private ?int $lockerId;

    private ?string $oohType;

    private ?string $name;

    private ?string $county;

    private ?string $city;

    private ?string $address;

    private ?string $postalCode;

    /**
     * @param ?int $lockerId
     * @param ?string $oohType
     * @param ?string $name
     * @param ?string $county
     * @param ?string $city
     * @param ?string $address
     * @param ?string $postalCode
     */
    public function __construct(
        ?int $lockerId = null,
        ?string $oohType = null,
        ?string $name = null,
        ?string $county = null,
        ?string $city = null,
        ?string $address = null,
        ?string $postalCode = null
    ) {
        $this->lockerId = $lockerId;
        $this->oohType = $oohType;
        $this->name = $name;
        $this->county = $county;
        $this->city = $city;
        $this->address = $address;
        $this->postalCode = $postalCode;
    }

    /**
     * @return ?int
     */
    public function getLockerId(): ?int
    {
        return $this->lockerId;
    }

    /**
     * @return ?string
     */
    public function getOohType(): ?string
    {
        return $this->oohType;
    }

    /**
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return ?string
     */
    public function getCounty(): ?string
    {
        return $this->county;
    }

    /**
     * @return ?string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return ?string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return ?string
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'lockerId' => $this->getLockerId(),
            'oohType' => $this->getOohType(),
            'name' => $this->getName(),
            'county' => $this->getCounty(),
            'city' => $this->getCity(),
            'address' => $this->getAddress(),
            'postalCode' => $this->getPostalCode(),
        ];
    }
}
