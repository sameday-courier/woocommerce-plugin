<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository;

interface RepositoryInterface
{
    /**
     * @return string
     */
    public static function getTableName(): string;
}
