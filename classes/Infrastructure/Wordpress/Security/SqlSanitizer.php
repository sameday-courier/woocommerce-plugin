<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

if (!defined('ABSPATH')) {
    exit;
}

final class SqlSanitizer
{
    /**
     * @param string $value
     *
     * @return string
     */
    public static function escSql(string $value): string
    {
        return esc_sql($value);
    }
}
