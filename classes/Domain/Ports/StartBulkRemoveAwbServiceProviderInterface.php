<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\StartBulkRemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\StartBulkRemoveAwbResponseDto;

interface StartBulkRemoveAwbServiceProviderInterface
{
    /**
     * @param StartBulkRemoveAwbRequestDto $startBulkRemoveAwbRequestDto
     *
     * @return StartBulkRemoveAwbResponseDto
     */
    public function start(StartBulkRemoveAwbRequestDto $startBulkRemoveAwbRequestDto): StartBulkRemoveAwbResponseDto;
}
