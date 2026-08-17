<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\PostRemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\PostRemoveAwbResponseDto;

interface PostRemoveAwbServiceProviderInterface
{
    /**
     * @param PostRemoveAwbRequestDto $postRemoveAwbRequestDto
     *
     * @return PostRemoveAwbResponseDto
     */
    public function apply(PostRemoveAwbRequestDto $postRemoveAwbRequestDto): PostRemoveAwbResponseDto;
}
