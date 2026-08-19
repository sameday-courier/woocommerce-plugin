<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class GetCountiesRequestDto
{
    private ?string $name;

    /**
     * @param ?string $name
     */
    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    /**
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
