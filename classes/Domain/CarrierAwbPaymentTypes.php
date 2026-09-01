<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

final class CarrierAwbPaymentTypes
{
    public const CLIENT = 1;

    /**
     * @var array<int, string>
     */
    private const LABEL_KEYS = [
        self::CLIENT => 'Client',
    ];

    /**
     * @return array<int, string>
     */
    public static function getLabelKeys(): array
    {
        return self::LABEL_KEYS;
    }
}
