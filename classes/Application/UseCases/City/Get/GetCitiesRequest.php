<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use Sameday\Sameday;

final class GetCitiesRequest
{
    /**
     * @var GetCitiesItem $getCitiesItem
     */
    private GetCitiesItem $getCitiesItem;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    public function __construct(
        GetCitiesItem $getCitiesItem,
        Sameday $sameday
    )
    {
        $this->getCitiesItem = $getCitiesItem;
        $this->sameday = $sameday;
    }

    /**
     * @return GetCitiesItem
     */
    public function getGetCitiesItem(): GetCitiesItem
    {
        return $this->getCitiesItem;
    }

    /**
     * @return Sameday
     */
    public function getSameday(): Sameday
    {
        return $this->sameday;
    }
}
