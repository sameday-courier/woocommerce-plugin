<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use SamedayCourier\Shipping\Domain\Models\ModelInterface;

/**
 * @template T of ModelInterface
 *
 * @implements MapperInterface<T>
 */
abstract class AbstractMapper implements MapperInterface
{
    /**
     * @param array $rows
     *
     * @return list<T>
     */
    public function mapCollection(array $rows): array
    {
        return array_map(
            function ($row) {
                return $this->map($row);
            },
            $rows
        );
    }
}
