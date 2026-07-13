<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;

if (!defined('ABSPATH')) {
    exit;
}

final class InputSanitizer
{
    /**
     * @param array $inputs
     *
     * @return array
     */
    public static function sanitizeInputs(array $inputs): array
    {
        $data = [];
        foreach ($inputs as $key => $input) {
            if (is_int($input) || is_bool($input)) {
                $data[$key] = $input;
            }

            if (is_string($input)) {
                $data[$key] = self::sanitizeInput($input);
            }

            if (is_array($input)) {
                $data[$key] = self::sanitizeInputs($input);
            }
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return string
     *
     * @throws JsonException
     */
    public static function sanitizeData(array $data): string
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
        }

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public static function sanitizeInput(string $input): string
    {
        return stripslashes(strip_tags(str_replace("'", '&#39;', $input)));
    }
}
