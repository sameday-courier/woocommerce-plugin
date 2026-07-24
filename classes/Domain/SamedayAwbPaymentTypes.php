<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

use Sameday\Objects\Types\AwbPaymentType;

if (!defined('ABSPATH')) {
    exit;
}

final class SamedayAwbPaymentTypes
{
    /**
     * @var array<int, string>
     */
    private const LABEL_KEYS = [
        AwbPaymentType::CLIENT => 'Client',
    ];

    /**
     * @return array<int, string>
     */
    public static function getLabelKeys(): array
    {
        return self::LABEL_KEYS;
    }
}
