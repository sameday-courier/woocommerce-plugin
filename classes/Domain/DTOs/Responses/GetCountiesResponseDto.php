<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

use Sameday\Objects\CountyObject;

final class GetCountiesResponseDto
{
    /**
     * @var CountyObject[]
     */
    private array $counties;

    /**
     * @param CountyObject[] $counties
     */
    public function __construct(array $counties)
    {
        $this->counties = $counties;
    }

    /**
     * @return CountyObject[]
     */
    public function getCounties(): array
    {
        return $this->counties;
    }
}
