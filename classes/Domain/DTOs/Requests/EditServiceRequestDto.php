<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class EditServiceRequestDto
{
    private int $id;

    private string $name;

    private string $price;

    private ?string $priceFree;

    private ?string $status;

    public function __construct(
        int $id,
        string $name,
        string $price,
        ?string $priceFree,
        ?string $status
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->priceFree = $priceFree;
        $this->status = $status;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getPriceFree(): ?string
    {
        return $this->priceFree;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }
}
