<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class ShowAsPdfResponseDto
{
    private string $pdf;

    /**
     * @param string $pdf
     */
    public function __construct(string $pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * @return string
     */
    public function getPdf(): string
    {
        return $this->pdf;
    }
}
