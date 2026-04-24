<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\Models\ModelInterface;

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
