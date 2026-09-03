<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

final class OpenPackageSessionNormalizer
{
    public const YES = 'yes';

    public const NO = 'no';

    /**
     * @param mixed $value
     *
     * @return self::YES|self::NO
     */
    public static function normalize($value): string
    {
        if (self::YES === $value || true === $value || 1 === $value || '1' === $value) {
            return self::YES;
        }

        return self::NO;
    }
}
