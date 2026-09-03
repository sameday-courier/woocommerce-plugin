<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class CarrierOptionalTaxDto
{
    private int $id;

    private string $code;

    private int $packageType;

    /**
     * @param int $id
     * @param string $code
     * @param int $packageType
     */
    public function __construct(int $id, string $code, int $packageType)
    {
        $this->id = $id;
        $this->code = $code;
        $this->packageType = $packageType;
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
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getPackageType(): int
    {
        return $this->packageType;
    }
}
