<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Traits;

if (!defined('ABSPATH')) {
    exit;
}

trait AddressObjectTrait
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

    private ?string $email;

    private ?string $phone;

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
     * @param string|null $email
     * @param string|null $phone
     */
    public function __construct(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $company = null,
        ?string $address1 = null,
        ?string $address2 = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postcode = null,
        ?string $country = null,
        ?string $email = null,
        ?string $phone = null
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->company = $company;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->city = $city;
        $this->state = $state;
        $this->postcode = $postcode;
        $this->country = $country;
        $this->email = $email;
        $this->phone = $phone;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['first_name']) ? (string) $data['first_name'] : null,
            isset($data['last_name']) ? (string) $data['last_name'] : null,
            isset($data['company']) ? (string) $data['company'] : null,
            isset($data['address_1']) ? (string) $data['address_1'] : null,
            isset($data['address_2']) ? (string) $data['address_2'] : null,
            isset($data['city']) ? (string) $data['city'] : null,
            isset($data['state']) ? (string) $data['state'] : null,
            isset($data['postcode']) ? (string) $data['postcode'] : null,
            isset($data['country']) ? (string) $data['country'] : null,
            isset($data['email']) ? (string) $data['email'] : null,
            isset($data['phone']) ? (string) $data['phone'] : null,
        );
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
