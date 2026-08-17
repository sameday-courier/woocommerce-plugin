<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use SamedayCourier\Shipping\Domain\Ports\GetCountiesServiceProviderInterface;

final class GetCountiesRequest
{
    private GetCountiesServiceProviderInterface $getCountiesServiceProvider;

    public function __construct(GetCountiesServiceProviderInterface $getCountiesServiceProvider)
    {
        $this->getCountiesServiceProvider = $getCountiesServiceProvider;
    }

    public function getGetCountiesServiceProvider(): GetCountiesServiceProviderInterface
    {
        return $this->getCountiesServiceProvider;
    }
}
