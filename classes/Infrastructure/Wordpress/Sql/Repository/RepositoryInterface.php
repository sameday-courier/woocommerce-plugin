<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository;

use SamedayCourier\Shipping\Infrastructure\Services\Mappers\MapperInterface;

interface RepositoryInterface
{
    /**
     * @return string
     */
    public function getTableName(): string;

    /**
     * @template T of MapperInterface
     *
     * @param class-string<T> $mapperClass
     *
     * @return T
     */
    public function getMapper(string $mapperClass): MapperInterface;
}
