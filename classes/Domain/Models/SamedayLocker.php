<?php

namespace SamedayCourier\Shipping\Domain\Models;

if (!defined( 'ABSPATH')) {
    exit;
}

final class SamedayLocker implements ModelInterface
{
    public int $id;
    public ?int $lockerId;
    public ?string $name;
    public ?string $county;
    public ?string $city;
    public ?string $address;
    public ?string $lat;
    public ?string $lng;
    public ?string $postalCode;
    public ?string $boxes;
    public ?bool $isTesting;

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
     * @return int|null
     */
    public function getLockerId(): ?int
    {
        return $this->lockerId;
    }

    /**
     * @param int|null $lockerId
     * 
     * @return self
     */
    public function setLockerId(?int $lockerId): self
    {
        $this->lockerId = $lockerId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * 
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

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
    public function getLat(): ?string
    {
        return $this->lat;
    }

    /**
     * @param string|null $lat
     * 
     * @return self
     */
    public function setLat(?string $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLng(): ?string
    {
        return $this->lng;
    }

    /**
     * @param string|null $lng
     * 
     * @return self
     */
    public function setLng(?string $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @param string|null $postalCode
     * 
     * @return self
     */
    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBoxes(): ?string
    {
        return $this->boxes;
    }

    /**
     * @param string|null $boxes
     * 
     * @return self
     */
    public function setBoxes(?string $boxes): self
    {
        $this->boxes = $boxes;

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
}
