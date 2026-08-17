<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RemoveAwbResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;

interface RemoveAwbServiceProviderInterface
{
    /**
     * @param RemoveAwbRequestDto $removeAwbRequestDto
     *
     * @return RemoveAwbResponseDto
     *
     * @throws CourierServiceException
     */
    public function remove(RemoveAwbRequestDto $removeAwbRequestDto): RemoveAwbResponseDto;
}
