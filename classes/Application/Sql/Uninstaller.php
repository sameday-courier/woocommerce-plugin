<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Sql;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Interfaces\DbHandlerInterface;

class Uninstaller
{
    /**
     * @var DbHandlerInterface $dbHandler
     */
    private $dbHandler;

    /**
     * @var SchemaDefinition
     */
    private SchemaDefinition $schemaDefinition;

    public function __construct()
    {
        $this->dbHandler = new DbHandler();
        $this->schemaDefinition = new SchemaDefinition();
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $samedayTables = $this->schemaDefinition->getSamedayTables();

        foreach ($samedayTables as $tableName) {
            $this->dbHandler->dropTable($tableName);
        }
    }
}
