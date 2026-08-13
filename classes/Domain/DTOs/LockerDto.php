<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use SamedayCourier\Shipping\Domain\Models\SamedayLocker;
use SamedayCourier\Shipping\Domain\SamedayLockerRules;

final class LockerDto
{
    private ?int $lockerId;

    private ?string $oohType;

    private ?string $name;

    private ?string $county;

    private ?string $city;

    private ?string $address;

    private ?string $postalCode;

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

    public static function fromSamedayLocker(SamedayLocker $locker): self
    {
        $lockerId = $locker->getLockerId();

        return new self(
            $lockerId,
            SamedayLockerRules::resolveOohType($lockerId),
            $locker->getName(),
            $locker->getCounty(),
            $locker->getCity(),
            $locker->getAddress(),
            $locker->getPostalCode(),
        );
    }

    public function getLockerId(): ?int
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

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

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
