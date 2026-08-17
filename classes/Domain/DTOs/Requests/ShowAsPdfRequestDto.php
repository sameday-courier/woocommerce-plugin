<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class ShowAsPdfRequestDto
{
    private string $awbNumber;

    private string $awbPdfType;

    public function __construct(string $awbNumber, string $awbPdfType)
    {
        $this->awbNumber = $awbNumber;
        $this->awbPdfType = $awbPdfType;
    }

    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }

    public function getAwbPdfType(): string
    {
        return $this->awbPdfType;
    }
}
