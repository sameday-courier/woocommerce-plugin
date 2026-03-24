<?php

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository;

interface RepositoryInterface
{
    /**
     * @return string
     */
    public function getTableName(): string;
}
