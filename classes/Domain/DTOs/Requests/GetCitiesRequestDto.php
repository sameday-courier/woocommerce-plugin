<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class GetCitiesRequestDto
{
    private ?int $countyId;

    private ?string $name;

    private ?string $postalCode;

    private int $page;

    public function __construct(
        ?int $countyId = null,
        ?string $name = null,
        ?string $postalCode = null,
        int $page = 1
    ) {
        $this->countyId = $countyId;
        $this->name = $name;
        $this->postalCode = $postalCode;
        $this->page = $page;
    }

    public function getCountyId(): ?int
    {
        return $this->countyId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
