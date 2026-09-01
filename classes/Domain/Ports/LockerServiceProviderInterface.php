<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\LockerDtoRequest;
use SamedayCourier\Shipping\Domain\DTOs\Responses\LockerDtoResponse;

interface LockerServiceProviderInterface
{
    /**
     * @param LockerDtoRequest $lockerRequest
     *
     * @return LockerDtoResponse
     */
    public function getLocker(LockerDtoRequest $lockerRequest): LockerDtoResponse;
}
