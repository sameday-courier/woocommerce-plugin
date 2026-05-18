<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\SamedayConstants;

if (!defined('ABSPATH')) {
    exit;
}

class TranslatorHandler
{
    /**
     * @param string $text
     *
     * @return string
     */
    public static function translate(string $text): string
    {
        return __($text, SamedayConstants::TEXT_DOMAIN);
    }
}
