<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Text;

final class RomanianDiacriticsNormalizer
{
    /**
     * @var array<int, string>
     */
    private const FROM = ['Ă', 'ă', 'Â', 'â', 'Î', 'î', 'Ș', 'ș', 'Ț', 'ț'];

    /**
     * @var array<int, string>
     */
    private const TO = ['A', 'a', 'A', 'a', 'I', 'i', 'S', 's', 'T', 't'];

    public static function normalize(string $value): string
    {
        return str_replace(self::FROM, self::TO, $value);
    }
}
