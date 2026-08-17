<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class GetCitiesRequest
{
    private GetCitiesItem $getCitiesItem;

    private CourierServiceProviderInterface $courierServiceProvider;

    public function __construct(
        GetCitiesItem $getCitiesItem,
        CourierServiceProviderInterface $courierServiceProvider
    ) {
        $this->getCitiesItem = $getCitiesItem;
        $this->courierServiceProvider = $courierServiceProvider;
    }

    public function getGetCitiesItem(): GetCitiesItem
    {
        return $this->getCitiesItem;
    }

    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }
}
