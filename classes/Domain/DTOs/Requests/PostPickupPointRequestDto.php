<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class PostPickupPointRequestDto
{
    private $countryId;

    private $countyId;

    private $cityId;

    private string $address;

    private string $postalCode;

    private string $alias;

    /**
     * @var array<int, array{name: string, phone: string, default: bool}>
     */
    private array $contactPersons;

    private bool $defaultPickupPoint;

    /**
     * @param mixed $countryId
     * @param mixed $countyId
     * @param mixed $cityId
     * @param array<int, array{name: string, phone: string, default?: bool}> $contactPersons
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
        $this->contactPersons = array_map(
            static function (array $contactPerson): array {
                return [
                    'name' => (string) ($contactPerson['name'] ?? ''),
                    'phone' => (string) ($contactPerson['phone'] ?? ''),
                    'default' => (bool) ($contactPerson['default'] ?? false),
                ];
            },
            $contactPersons
        );
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
     * @return array<int, array{name: string, phone: string, default: bool}>
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
