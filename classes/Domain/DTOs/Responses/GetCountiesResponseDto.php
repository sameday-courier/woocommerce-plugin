<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class GetCountiesResponseDto
{
    /**
     * @var array<int, array{id: int, name: string}>
     */
    private array $counties;

    /**
     * @param array $counties
     */
    public function __construct(array $counties)
    {
        $this->counties = $counties;
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function getCounties(): array
    {
        return $this->counties;
    }
}
