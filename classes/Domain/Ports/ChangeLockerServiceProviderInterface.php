<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\ChangeLockerRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\ChangeLockerResponseDto;

interface ChangeLockerServiceProviderInterface
{
    /**
     * @param ChangeLockerRequestDto $changeLockerRequestDto
     *
     * @return ChangeLockerResponseDto
     */
    public function change(ChangeLockerRequestDto $changeLockerRequestDto): ChangeLockerResponseDto;
}
