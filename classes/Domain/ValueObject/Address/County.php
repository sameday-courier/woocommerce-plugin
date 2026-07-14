<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\ValueObject\Address;

use SamedayCourier\Shipping\Domain\DTOs\CountyDto;

if (!defined('ABSPATH')) {
    exit;
}

final class County
{
    private function __construct()
    {
    }

    public static function fromName(?string $name): CountyDto
    {
        if (null === $name || '' === $name) {
            return new CountyDto(null);
        }

        return new CountyDto($name);
    }
}
