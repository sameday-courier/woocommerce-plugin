<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql;

if (!defined( 'ABSPATH')) {
    exit;
}

class Installer
{
    private SchemaHandler $schemaHandler;

    public function __construct()
    {
        $this->schemaHandler = new SchemaHandler();
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->schemaHandler->createTables();
        $this->schemaHandler->alterTables();
    }
}
