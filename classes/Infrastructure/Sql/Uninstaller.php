<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandlerInterface;

class Uninstaller
{
    /**
     * @var DbHandlerInterface $dbHandler
     */
    private $dbHandler;

    public function __construct()
    {
        $this->dbHandler = new DbHandler();
    }

    public function run(): void
    {
        foreach (SchemaDefinition::getSamedayTables() as $tableName) {
            $this->dbHandler->dropTable($tableName);
        }
    }
}
