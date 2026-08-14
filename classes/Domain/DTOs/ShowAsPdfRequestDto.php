<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use Sameday\Objects\Types\AwbPdfType;

final class ShowAsPdfRequestDto
{
    private string $awbNumber;

    private AwbPdfType $awbPdfType;

    public function __construct(string $awbNumber, AwbPdfType $awbPdfType)
    {
        $this->awbNumber = $awbNumber;
        $this->awbPdfType = $awbPdfType;
    }

    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }

    public function getAwbPdfType(): AwbPdfType
    {
        return $this->awbPdfType;
    }
}
