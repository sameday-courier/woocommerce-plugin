<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Traits;

trait AddressObjectTrait
{
    private ?string $firstName;

    private ?string $lastName;

    private ?string $company;

    private ?string $address1;

    private ?string $address2;

    private ?string $city;

    private ?string $county;

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
     * @param string|null $county
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
        ?string $county = null,
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
        $this->county = $county;
        $this->postcode = $postcode;
        $this->country = $country;
        $this->email = $email;
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        $name = trim(sprintf(
            '%s %s',
            ltrim($this->firstName ?? ''),
            ltrim($this->lastName ?? '')
        ));

        return '' !== $name ? $name : null;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return ?string
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        $address1 = $this->address1 ?? "";
        $address2 = $this->address2 ?? "";

        return sprintf('%s %s', ltrim($address1), ltrim($address2));
    }

    /**
     * @return ?string
     */
    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    /**
     * @return ?string
     */
    public function getAddress2(): ?string
    {
        return $this->address2;
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
    public function getCounty(): ?string
    {
        return $this->county;
    }

    /**
     * @return ?string
     */
    public function getState(): ?string
    {
        return $this->county;
    }

    /**
     * @return ?string
     */
    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * @return ?string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @return ?string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return ?string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
