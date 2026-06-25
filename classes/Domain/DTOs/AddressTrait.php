<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

trait AddressTrait
{
    private ?string $firstName;

    private ?string $lastName;

    private ?string $company;

    private ?string $address1;

    private ?string $address2;

    private ?string $city;

    private ?string $state;

    private ?string $postcode;

    private ?string $country;

    /**
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $company
     * @param string|null $address1
     * @param string|null $address2
     * @param string|null $city
     * @param string|null $state
     * @param string|null $postcode
     * @param string|null $country
     */
    protected function initializeAddress(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $company = null,
        ?string $address1 = null,
        ?string $address2 = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postcode = null,
        ?string $country = null
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->company = $company;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->city = $city;
        $this->state = $state;
        $this->postcode = $postcode;
        $this->country = $country;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array{
     *     0: string|null,
     *     1: string|null,
     *     2: string|null,
     *     3: string|null,
     *     4: string|null,
     *     5: string|null,
     *     6: string|null,
     *     7: string|null,
     *     8: string|null
     * }
     */
    protected static function mapAddressFromArray(array $data): array
    {
        return [
            isset($data['first_name']) ? (string) $data['first_name'] : null,
            isset($data['last_name']) ? (string) $data['last_name'] : null,
            isset($data['company']) ? (string) $data['company'] : null,
            isset($data['address_1']) ? (string) $data['address_1'] : null,
            isset($data['address_2']) ? (string) $data['address_2'] : null,
            isset($data['city']) ? (string) $data['city'] : null,
            isset($data['state']) ? (string) $data['state'] : null,
            isset($data['postcode']) ? (string) $data['postcode'] : null,
            isset($data['country']) ? (string) $data['country'] : null,
        ];
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }
}
