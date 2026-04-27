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
        return array_map(
            function ($row) {
                $this->map($row);
            },
            $rows
        );
    }
}
