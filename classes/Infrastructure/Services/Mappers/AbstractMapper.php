<?php

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use SamedayCourier\Shipping\Domain\ModelInterface;

abstract class AbstractMapper implements MapperInterface
{
    /**
     * @param array $rows
     *
     * @return ModelInterface[]
     */
    public function mapCollection(array $rows): array
    {
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->map($row);
        }

        return $collection;
    }
}
