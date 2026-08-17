<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\CourierServiceDto;
use SamedayCourier\Shipping\Domain\Models\CarrierService;

interface ServiceCatalogStoreServiceProviderInterface extends CarrierServiceProviderInterface
{
    /**
     * @param int $id
     *
     * @return CarrierService|null
     */
    public function getById(int $id): ?CarrierService;

    /**
     * @param int $samedayId
     *
     * @return CarrierService|null
     */
    public function getBySamedayId(int $samedayId): ?CarrierService;

    /**
     * @param string $samedayCode
     *
     * @return CarrierService|null
     */
    public function getByCode(string $samedayCode): ?CarrierService;

    /**
     * @return CarrierService[]
     */
    public function getAll(): array;

    /**
     * @param CourierServiceDto $service
     *
     * @return void
     */
    public function add(CourierServiceDto $service): void;

    /**
     * @param CourierServiceDto $service
     * @param int $localId
     *
     * @return bool
     */
    public function updateFromRemote(CourierServiceDto $service, int $localId): bool;

    /**
     * @param int $id
     * @param array<string, mixed> $fields
     *
     * @return bool
     */
    public function updateFields(int $id, array $fields): bool;

    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteById(int $id): void;
}
