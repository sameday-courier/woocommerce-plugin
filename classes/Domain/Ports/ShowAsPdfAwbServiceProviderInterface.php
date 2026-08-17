<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowAsPdfAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\ShowAsPdfAwbResponseDto;

interface ShowAsPdfAwbServiceProviderInterface
{
    /**
     * @param ShowAsPdfAwbRequestDto $showAsPdfAwbRequestDto
     *
     * @return ShowAsPdfAwbResponseDto
     */
    public function showAsPdf(ShowAsPdfAwbRequestDto $showAsPdfAwbRequestDto): ShowAsPdfAwbResponseDto;
}
