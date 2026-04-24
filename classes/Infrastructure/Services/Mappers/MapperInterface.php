<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\Models\ModelInterface;

interface MapperInterface
{
    /**
     * @param array $row
     *
     * @return ModelInterface
     */
    public function map(array $row): ModelInterface;

    /**
     * @param array $rows
     *
     * @return ModelInterface[]
     */
    public function mapCollection(array $rows): array;
}
