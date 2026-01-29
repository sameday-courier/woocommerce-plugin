<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

class Uninstaller
{
    public static function run(): void
    {
        global $wpdb;

        foreach (SchemaMapper::getSamedayTables() as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}
