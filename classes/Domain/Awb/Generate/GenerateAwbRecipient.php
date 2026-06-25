<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate;

use Sameday\Objects\PostAwb\Request\CompanyEntityObject;

if (!defined('ABSPATH')) {
    exit;
}

final class AwbRecipient
{
    private string $city;

    private string $county;

    private string $address;

    private string $name;

    private string $phone;

    private string $email;

    private ?string $postalCode;

    private ?string $address1;

    private ?string $address2;

    private ?string $state;

    private ?string $country;

    private ?CompanyEntityObject $company;

    public function __construct(
        string $city,
        string $county,
        string $address,
        string $name,
        string $phone,
        string $email,
        ?string $postalCode,
        ?string $address1,
        ?string $address2,
        ?string $state,
        ?string $country,
        ?CompanyEntityObject $company = null
    ) {
        $this->city = $city;
        $this->county = $county;
        $this->address = $address;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->postalCode = $postalCode;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->state = $state;
        $this->country = $country;
        $this->company = $company;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCounty(): string
    {
        return $this->county;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCompany(): ?CompanyEntityObject
    {
        return $this->company;
    }
}
