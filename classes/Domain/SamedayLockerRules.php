<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

if (!defined('ABSPATH')) {
    exit;
}

final class SamedayLockerRules
{
    private const OOH_TYPE_PUDO_THRESHOLD = 500000;

    private const OOH_TYPE_LOCKER = '0';

    private const OOH_TYPE_PUDO = '1';

    /**
     * @param int|null $lockerId
     *
     * @return string|null
     */
    public static function resolveOohType(?int $lockerId): ?string
    {
        if (null === $lockerId) {
            return null;
        }

        return $lockerId >= self::OOH_TYPE_PUDO_THRESHOLD
            ? self::OOH_TYPE_PUDO
            : self::OOH_TYPE_LOCKER;
    }
}
