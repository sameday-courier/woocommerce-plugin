<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class GetCountiesRequest
{
    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @param CourierServiceProviderInterface $courierServiceProvider
     */
    public function __construct(CourierServiceProviderInterface $courierServiceProvider)
    {
        $this->courierServiceProvider = $courierServiceProvider;
    }

    /**
     * @return CourierServiceProviderInterface
     */
    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }
}
