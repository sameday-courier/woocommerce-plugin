<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

final class CarrierPackageTypes
{
    public const PARCEL = 0;
    public const ENVELOPE = 1;
    public const LARGE = 2;

    /**
     * @var array<int, string>
     */
    private const LABEL_KEYS = [
        self::PARCEL => 'Parcel',
        self::ENVELOPE => 'Envelope',
        self::LARGE => 'Large package',
    ];

    /**
     * @return array<int, string>
     */
    public static function getLabelKeys(): array
    {
        return self::LABEL_KEYS;
    }
}
