<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class GetCountiesRequest
{
    private CourierServiceProviderInterface $courier;

    public function __construct(CourierServiceProviderInterface $courier)
    {
        $this->courier = $courier;
    }

    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }
}
