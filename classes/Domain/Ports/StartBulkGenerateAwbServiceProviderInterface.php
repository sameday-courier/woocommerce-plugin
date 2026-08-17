<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\StartBulkGenerateAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\StartBulkGenerateAwbResponseDto;

interface StartBulkGenerateAwbServiceProviderInterface
{
    /**
     * @param StartBulkGenerateAwbRequestDto $startBulkGenerateAwbRequestDto
     *
     * @return StartBulkGenerateAwbResponseDto
     */
    public function start(StartBulkGenerateAwbRequestDto $startBulkGenerateAwbRequestDto): StartBulkGenerateAwbResponseDto;
}
