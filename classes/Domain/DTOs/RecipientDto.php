<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use SamedayCourier\Shipping\Domain\DTOs\Traits\AddressObjectTrait;

if (!defined('ABSPATH')) {
    exit;
}

final class RecipientDto
{
    use AddressObjectTrait;

    /**
     * @var string $address
     */
    public string $address;

    /**
     * @var string $name
     */
    public string $name;

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @param string|null $company
     * @return $this
     */
    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @param string|null $address1
     * @return $this
     */
    public function setAddress1(?string $address1): self
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * @param string|null $address2
     * @return $this
     */
    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function setState(?string $state): self
    {
        $this->county = $state;

        return $this;
    }

    /**
     * @param string|null $country
     *
     * @return $this
     */
    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string|null $phone
     *
     * @return $this
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @param string|null $county
     *
     * @return $this
     */
    public function setCounty(?string $county): self
    {
        $this->county = $county;

        return $this;
    }

    /**
     * @param string|null $address
     *
     * @return $this
     */
    public function setAddress(?string $address): self
    {
        $this->address1 = $address;
        $this->address2 = null;

        return $this;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): self
    {
        $this->firstName = $name;
        $this->lastName = null;

        return $this;
    }

    /**
     * @param string|null $postalCode
     *
     * @return $this
     */
    public function setPostalCode(?string $postalCode): self
    {
        $this->postcode = $postalCode;

        return $this;
    }
}
