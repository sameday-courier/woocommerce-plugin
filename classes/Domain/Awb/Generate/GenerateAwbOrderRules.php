<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbOrderRules
{
    /**
     * @param array<int, mixed> $shippingLines
     */
    public static function validateShippingLines(array $shippingLines): ValidationResult
    {
        if ([] === $shippingLines) {
            return new ValidationResult(['No shipping lines for this awb item.']);
        }

        return new ValidationResult();
    }
}
