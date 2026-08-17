<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowHistoryAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\ShowHistoryAwbResponseDto;

interface ShowHistoryAwbServiceProviderInterface
{
    /**
     * @param ShowHistoryAwbRequestDto $showHistoryAwbRequestDto
     *
     * @return ShowHistoryAwbResponseDto
     */
    public function showHistory(ShowHistoryAwbRequestDto $showHistoryAwbRequestDto): ShowHistoryAwbResponseDto;
}
