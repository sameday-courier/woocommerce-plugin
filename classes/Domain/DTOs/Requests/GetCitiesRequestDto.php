<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class GetCitiesRequestDto
{
    private ?int $countyId;

    private ?string $name;

    private ?string $postalCode;

    private int $page;

    /**
     * @param ?int $countyId
     * @param ?string $name
     * @param ?string $postalCode
     * @param int $page
     */
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

    /**
     * @return ?int
     */
    public function getCountyId(): ?int
    {
        return $this->countyId;
    }

    /**
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return ?string
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }
}
