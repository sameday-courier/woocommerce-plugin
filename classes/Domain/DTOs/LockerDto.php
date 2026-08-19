<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use SamedayCourier\Shipping\Domain\Models\CarrierLocker;
use SamedayCourier\Shipping\Domain\CarrierLockerRules;

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
     * @param array<string, mixed> $data
     */
    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['lockerId']) && $data['lockerId'] !== ''
                ? (int) $data['lockerId']
                : null,
            isset($data['oohType']) && $data['oohType'] !== ''
                ? (string) $data['oohType']
                : null,
            isset($data['name']) ? (string) $data['name'] : null,
            isset($data['county']) ? (string) $data['county'] : null,
            isset($data['city']) ? (string) $data['city'] : null,
            isset($data['address']) ? (string) $data['address'] : null,
            isset($data['postalCode']) ? (string) $data['postalCode'] : null,
        );
    }

    /**
     * @param CarrierLocker $locker
     *
     * @return self
     */
    public static function fromSamedayLocker(CarrierLocker $locker): self
    {
        $lockerId = $locker->getLockerId();

        return new self(
            $lockerId,
            CarrierLockerRules::resolveOohType($lockerId),
            $locker->getName(),
            $locker->getCounty(),
            $locker->getCity(),
            $locker->getAddress(),
            $locker->getPostalCode(),
        );
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
     * @return array<string,
     */
    public function toArray(): array
    {
        return [
            'lockerId' => $this->lockerId,
            'oohType' => $this->oohType,
            'name' => $this->name,
            'county' => $this->county,
            'city' => $this->city,
            'address' => $this->address,
            'postalCode' => $this->postalCode,
        ];
    }
}
