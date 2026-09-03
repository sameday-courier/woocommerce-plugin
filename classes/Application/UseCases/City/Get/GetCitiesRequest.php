<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

final class GetCitiesRequest implements RequestInterface
{
    /**
     * @var int $countyId
     */
    private int $countyId;

    /**
     * @param int $countyId
     */
    public function __construct(int $countyId)
    {
        $this->countyId = $countyId;
    }

    /**
     * @return int
     */
    public function getCountyId(): int
    {
        return $this->countyId;
    }
}
