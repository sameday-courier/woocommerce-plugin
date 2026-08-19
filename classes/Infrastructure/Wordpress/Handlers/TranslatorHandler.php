<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use SamedayCourier\Shipping\Domain\CarrierConstants;

final class TranslatorHandler
{
    /**
     * @param string $text
     *
     * @return string
     */
    /**
     * @param string $text
     *
     * @return string
     */
    public static function translate(string $text): string
    {
        return esc_html(__($text, CarrierConstants::TEXT_DOMAIN));
    }
}
