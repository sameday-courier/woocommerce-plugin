<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class ShowAsPdfRequestDto
{
    private string $awbNumber;

    private string $awbPdfType;

    /**
     * @param string $awbNumber
     * @param string $awbPdfType
     */
    public function __construct(string $awbNumber, string $awbPdfType)
    {
        $this->awbNumber = $awbNumber;
        $this->awbPdfType = $awbPdfType;
    }

    /**
     * @return string
     */
    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }

    /**
     * @return string
     */
    public function getAwbPdfType(): string
    {
        return $this->awbPdfType;
    }
}
