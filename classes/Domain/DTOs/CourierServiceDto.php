<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class CourierServiceDto
{
    private int $id;

    private string $name;

    private string $code;

    private ?string $serializedOptionalTaxes;

    public function __construct(
        int $id,
        string $name,
        string $code,
        ?string $serializedOptionalTaxes = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->serializedOptionalTaxes = $serializedOptionalTaxes;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getSerializedOptionalTaxes(): ?string
    {
        return $this->serializedOptionalTaxes;
    }
}
