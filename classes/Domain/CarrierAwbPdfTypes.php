<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

final class CarrierAwbPdfTypes
{
    public const A4 = 'A4';
    public const A6 = 'A6';

    /**
     * @var array<string, string>
     */
    private const LABEL_KEYS = [
        self::A4 => 'A4',
        self::A6 => 'A6',
    ];

    /**
     * @return array<string, string>
     */
    public static function getLabelKeys(): array
    {
        return self::LABEL_KEYS;
    }
}
