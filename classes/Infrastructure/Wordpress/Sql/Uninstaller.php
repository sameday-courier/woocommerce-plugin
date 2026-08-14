<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\DbHandlerInterface;

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
