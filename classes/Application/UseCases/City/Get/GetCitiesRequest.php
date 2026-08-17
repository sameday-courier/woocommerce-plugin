<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Domain\Ports\GetCitiesServiceProviderInterface;

final class GetCitiesRequest
{
    private GetCitiesItem $getCitiesItem;

    private GetCitiesServiceProviderInterface $getCitiesServiceProvider;

    public function __construct(
        GetCitiesItem $getCitiesItem,
        GetCitiesServiceProviderInterface $getCitiesServiceProvider
    ) {
        $this->getCitiesItem = $getCitiesItem;
        $this->getCitiesServiceProvider = $getCitiesServiceProvider;
    }

    public function getGetCitiesItem(): GetCitiesItem
    {
        return $this->getCitiesItem;
    }

    public function getGetCitiesServiceProvider(): GetCitiesServiceProviderInterface
    {
        return $this->getCitiesServiceProvider;
    }
}
