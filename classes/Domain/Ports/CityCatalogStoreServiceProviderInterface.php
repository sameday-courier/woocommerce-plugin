<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\CitySourceDto;

interface CityCatalogStoreServiceProviderInterface
{
    /**
     * @return void
     */
    public function ensureTableExists(): void;

    /**
     * @return void
     */
    public function truncate(): void;

    /**
     * @param CitySourceDto $city
     *
     * @return void
     */
    public function insert(CitySourceDto $city): void;

    /**
     * @return void
     */
    public function refreshCache(): void;
}
