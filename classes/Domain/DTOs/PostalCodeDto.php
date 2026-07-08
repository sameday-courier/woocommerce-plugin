<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class PostalCodeDto
{
    private string $code;

    /**
     * @param string|null $code
     */
    public function __construct(?string $code)
    {
        $this->code = $code;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }
}
