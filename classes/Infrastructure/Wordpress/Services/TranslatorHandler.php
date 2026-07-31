<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\SamedayConstants;

if (!defined('ABSPATH')) {
    exit;
}

final class TranslatorHandler
{
    /**
     * @param string $text
     *
     * @return string
     */
    public static function translate(string $text): string
    {
        return esc_html(__($text, SamedayConstants::TEXT_DOMAIN));
    }
}
