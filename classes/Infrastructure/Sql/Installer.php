<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

class Installer
{
    /**
     * @return void
     */
    public static function run(): void
    {
        SchemaHandler::createTables();
        SchemaHandler::alterTables();
    }
}
