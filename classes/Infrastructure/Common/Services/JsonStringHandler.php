<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Common\Services;

if (!defined('ABSPATH')) {
    exit;
}

final class JsonStringHandler
{
    /**
     * @param string $jsonString
     *
     * @return string
     */
    public static function fixJson(string $jsonString): string
    {
        $pattern = '/(":\s*")([^"]*(?:"[^"]*)*?)("(?=\s*[,}\]]))/';

        return preg_replace_callback(
            $pattern,
            static function ($matches) {
                return $matches[1] . str_replace('"', '\"', $matches[2]) . $matches[3];
            },
            $jsonString
        );
    }
}
