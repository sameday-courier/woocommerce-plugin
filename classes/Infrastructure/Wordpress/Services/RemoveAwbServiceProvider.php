<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RemoveAwbResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\RemoveAwbServiceProviderInterface;

final class RemoveAwbServiceProvider implements RemoveAwbServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    public function __construct(?CourierServiceProviderInterface $courier = null)
    {
        $this->courier = $courier ?? new CourierServiceProvider();
    }

    /**
     * @param RemoveAwbRequestDto $removeAwbRequestDto
     *
     * @return RemoveAwbResponseDto
     *
     * @throws CourierServiceException
     */
    public function remove(RemoveAwbRequestDto $removeAwbRequestDto): RemoveAwbResponseDto
    {
        return $this->courier->removeAwb($removeAwbRequestDto);
    }
}
