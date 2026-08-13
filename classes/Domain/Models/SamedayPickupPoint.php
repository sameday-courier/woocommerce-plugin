<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Models;

final class SamedayPickupPoint implements ModelInterface
{
    public int $id;
    public int $samedayId;
    public ?string $samedayAlias;
    public ?bool $isTesting;
    public ?string $city;
    public ?string $county;
    public ?string $address;
    public ?string $contactPersons;
    public ?bool $defaultPickupPoint;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * 
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getSamedayId(): int
    {
        return $this->samedayId;
    }

    /**
     * @param int $samedayId
     * 
     * @return self
     */
    public function setSamedayId(int $samedayId): self
    {
        $this->samedayId = $samedayId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSamedayAlias(): ?string
    {
        return $this->samedayAlias;
    }

    /**
     * @param string|null $samedayAlias
     * 
     * @return self
     */
    public function setSamedayAlias(?string $samedayAlias): self
    {
        $this->samedayAlias = $samedayAlias;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsTesting(): ?bool
    {
        return $this->isTesting;
    }

    /**
     * @param bool|null $isTesting
     * 
     * @return self
     */
    public function setIsTesting(?bool $isTesting): self
    {
        $this->isTesting = $isTesting;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     * 
     * @return self
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCounty(): ?string
    {
        return $this->county;
    }

    /**
     * @param string|null $county
     * 
     * @return self
     */
    public function setCounty(?string $county): self
    {
        $this->county = $county;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * 
     * @return self
     */
    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContactPersons(): ?string
    {
        return $this->contactPersons;
    }

    /**
     * @param string|null $contactPersons
     * 
     * @return self
     */
    public function setContactPersons(?string $contactPersons): self
    {
        $this->contactPersons = $contactPersons;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getDefaultPickupPoint(): ?bool
    {
        return $this->defaultPickupPoint;
    }

    /**
     * @param bool|null $defaultPickupPoint
     * 
     * @return self
     */
    public function setDefaultPickupPoint(?bool $defaultPickupPoint): self
    {
        $this->defaultPickupPoint = $defaultPickupPoint;

        return $this;
    }
}
