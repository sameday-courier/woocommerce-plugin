<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository;

use SamedayCourier\Shipping\Infrastructure\Services\Mappers\MapperInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\DbHandlerInterface;

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
     * @template T of MapperInterface
     *
     * @param class-string<T> $mapperClass
     *
     * @return T
     */
    public function getMapper(string $mapperClass): MapperInterface
    {
        return new $mapperClass();
    }
}
