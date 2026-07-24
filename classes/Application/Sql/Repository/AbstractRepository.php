<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Sql\Repository;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Infrastructure\Services\Mappers\MapperInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Interfaces\DbHandlerInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var DbHandlerInterface $dbHandler
     */
    protected DbHandlerInterface $dbHandler;

    /**
     * @param DbHandlerInterface|null $dbHandler
     */
    public function __construct(DbHandlerInterface $dbHandler = null)
    {
        if (null === $dbHandler) {
            $this->dbHandler = new DbHandler();
        } else {
            $this->dbHandler = $dbHandler;
        }
    }

    /**
     * @param string $mapperClass
     *
     * @return MapperInterface
     */
    public function getMapper(string $mapperClass): MapperInterface
    {
        return new $mapperClass();
    }
}
