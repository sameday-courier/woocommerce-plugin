<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class CourierLockerDto
{
    private int $id;

    private string $name;

    private string $city;

    private string $county;

    private string $address;

    private string $lat;

    private string $lng;

    private string $postalCode;

    private ?string $serializedBoxes;

    /**
     * @param int $id
     * @param string $name
     * @param string $city
     * @param string $county
     * @param string $address
     * @param string $lat
     * @param string $lng
     * @param string $postalCode
     * @param ?string $serializedBoxes
     */
    public function __construct(
        int $id,
        string $name,
        string $city,
        string $county,
        string $address,
        string $lat,
        string $lng,
        string $postalCode,
        ?string $serializedBoxes = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->city = $city;
        $this->county = $county;
        $this->address = $address;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->postalCode = $postalCode;
        $this->serializedBoxes = $serializedBoxes;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getCounty(): string
    {
        return $this->county;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getLat(): string
    {
        return $this->lat;
    }

    /**
     * @return string
     */
    public function getLng(): string
    {
        return $this->lng;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @return ?string
     */
    public function getSerializedBoxes(): ?string
    {
        return $this->serializedBoxes;
    }
}
