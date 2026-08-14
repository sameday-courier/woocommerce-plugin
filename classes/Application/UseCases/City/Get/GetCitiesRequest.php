<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class GetCitiesRequest
{
    /**
     * @var GetCitiesItem $getCitiesItem
     */
    private GetCitiesItem $getCitiesItem;

    private CourierServiceProviderInterface $courier;

    public function __construct(
        GetCitiesItem $getCitiesItem,
        CourierServiceProviderInterface $courier
    )
    {
        $this->getCitiesItem = $getCitiesItem;
        $this->courier = $courier;
    }

    /**
     * @return GetCitiesItem
     */
    public function getGetCitiesItem(): GetCitiesItem
    {
        return $this->getCitiesItem;
    }

    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }
}
