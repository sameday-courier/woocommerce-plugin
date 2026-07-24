<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

use Sameday\Objects\Types\PackageType;

if (!defined('ABSPATH')) {
    exit;
}

final class SamedayPackageTypes
{
    /**
     * @var array<int, string>
     */
    private const LABEL_KEYS = [
        PackageType::PARCEL => 'Parcel',
        PackageType::ENVELOPE => 'Envelope',
        PackageType::LARGE => 'Large package',
    ];

    /**
     * @return array<int, string>
     */
    public static function getLabelKeys(): array
    {
        return self::LABEL_KEYS;
    }
}
