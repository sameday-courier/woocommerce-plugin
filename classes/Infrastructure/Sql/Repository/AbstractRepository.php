<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandlerInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var DbHandlerInterface $dbHandler
     */
    protected DbHandlerInterface $dbHandler;
    public function __construct(DbHandlerInterface $dbHandler)
    {
        $this->dbHandler = $dbHandler;
    }
}
