<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

class Uninstaller
{
    public static function run(): void
    {
        foreach (SchemaDefinition::getSamedayTables() as $tableName) {
            DbHandler::dropTable($tableName);
        }
    }
}
