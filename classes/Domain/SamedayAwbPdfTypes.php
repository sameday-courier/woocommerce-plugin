<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

use Sameday\Objects\Types\AwbPdfType;

if (!defined('ABSPATH')) {
    exit;
}

final class SamedayAwbPdfTypes
{
    /**
     * @var array<string, string>
     */
    private const LABEL_KEYS = [
        AwbPdfType::A4 => 'A4',
        AwbPdfType::A6 => 'A6',
    ];

    /**
     * @return array<string, string>
     */
    public static function getLabelKeys(): array
    {
        return self::LABEL_KEYS;
    }
}
