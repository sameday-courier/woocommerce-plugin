<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

use JsonException;

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
            if (is_array($input)) {
                $data[$key] = self::sanitizeInputs($input);
                continue;
            }

            if (is_string($input)) {
                $data[$key] = self::sanitizeInput($input);
                continue;
            }

            if (is_int($input) || is_float($input) || is_bool($input)) {
                $data[$key] = $input;
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
        if ([] !== $data) {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = self::sanitizeInput($value);
                }
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
        return sanitize_text_field(wp_unslash($input));
    }
}
