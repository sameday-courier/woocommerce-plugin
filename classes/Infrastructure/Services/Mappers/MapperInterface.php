<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use SamedayCourier\Shipping\Domain\Models\ModelInterface;

/**
 * @template T of ModelInterface
 */
interface MapperInterface
{
    /**
     * @param array $row
     *
     * @return T
     */
    public function map(array $row): ModelInterface;

    /**
     * @param array $rows
     *
     * @return list<T>
     */
    public function mapCollection(array $rows): array;
}
