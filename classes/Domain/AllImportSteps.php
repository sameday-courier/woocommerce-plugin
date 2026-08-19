<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

final class AllImportSteps
{
    public const SERVICES = 1;

    public const PICKUP_POINTS = 2;

    public const LOCKERS = 3;

    public const CITIES = 4;

    /**
     * @return int[]
     */
    /**
     * @return array
     */
    public static function ids(): array
    {
        return [
            self::SERVICES,
            self::PICKUP_POINTS,
            self::LOCKERS,
            self::CITIES,
        ];
    }
}
