<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

class SchemaMapper
{
    private const SAMEDAY_TABLES = [
        'sameday_awb',
        'sameday_pickup_point',
        'sameday_service',
        'sameday_package',
        'sameday_locker',
        'sameday_cities',
    ];

    /**
     * @return string[]
     */
    public static function getSamedayTables(): array
    {
        global $wpdb;

        return array_map(
            static function (string $table) use ($wpdb) {
                return $wpdb->prefix . $table;
            },
            self::SAMEDAY_TABLES
        );
    }
}
