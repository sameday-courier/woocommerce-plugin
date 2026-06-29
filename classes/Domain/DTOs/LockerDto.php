<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class LockerDto
{
    private ?string $lockerId;

    private ?string $oohType;

    private ?string $name;

    private ?string $address;

    private ?string $cityId;

    private ?string $city;

    private ?string $countyId;

    private ?string $county;

    private ?string $supportedPayment;

    private ?string $postalCode;

    /**
     * @param string|null $lockerId
     * @param string|null $oohType
     * @param string|null $name
     * @param string|null $address
     * @param string|null $cityId
     * @param string|null $city
     * @param string|null $countyId
     * @param string|null $county
     * @param string|null $supportedPayment
     * @param string|null $postalCode
     */
    public function __construct(
        ?string $lockerId = null,
        ?string $oohType = null,
        ?string $name = null,
        ?string $address = null,
        ?string $cityId = null,
        ?string $city = null,
        ?string $countyId = null,
        ?string $county = null,
        ?string $supportedPayment = null,
        ?string $postalCode = null
    ) {
        $this->lockerId = $lockerId;
        $this->oohType = $oohType;
        $this->name = $name;
        $this->address = $address;
        $this->cityId = $cityId;
        $this->city = $city;
        $this->countyId = $countyId;
        $this->county = $county;
        $this->supportedPayment = $supportedPayment;
        $this->postalCode = $postalCode;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['lockerId']) ? (string) $data['lockerId'] : null,
            isset($data['oohType']) ? (string) $data['oohType'] : null,
            isset($data['name']) ? (string) $data['name'] : null,
            isset($data['address']) ? (string) $data['address'] : null,
            isset($data['cityId']) ? (string) $data['cityId'] : null,
            isset($data['city']) ? (string) $data['city'] : null,
            isset($data['countyId']) ? (string) $data['countyId'] : null,
            isset($data['county']) ? (string) $data['county'] : null,
            isset($data['supportedPayment']) ? (string) $data['supportedPayment'] : null,
            isset($data['postalCode']) ? (string) $data['postalCode'] : null,
        );
    }

    public function getLockerId(): ?string
    {
        return $this->lockerId;
    }

    public function getOohType(): ?string
    {
        return $this->oohType;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getCityId(): ?string
    {
        return $this->cityId;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountyId(): ?string
    {
        return $this->countyId;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function getSupportedPayment(): ?string
    {
        return $this->supportedPayment;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }
}
