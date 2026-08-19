<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\CourierServiceDto;
use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\ServiceCatalogStoreServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class ServiceCatalogStoreServiceProvider implements ServiceCatalogStoreServiceProviderInterface
{
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param ?SamedayServiceRepository $samedayServiceRepository
     */
    public function __construct(?SamedayServiceRepository $samedayServiceRepository = null)
    {
        $this->samedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository();
    }

    /**
     * @param int $id
     *
     * @return ?CarrierService
     */
    public function getById(int $id): ?CarrierService
    {
        return $this->samedayServiceRepository->getServiceById($id);
    }

    /**
     * @param int $samedayId
     *
     * @return ?CarrierService
     */
    public function getBySamedayId(int $samedayId): ?CarrierService
    {
        return $this->samedayServiceRepository->getServiceSameday($samedayId);
    }

    /**
     * @param string $samedayCode
     *
     * @return ?CarrierService
     */
    public function getByCode(string $samedayCode): ?CarrierService
    {
        return $this->samedayServiceRepository->getServiceSamedayByCode($samedayCode);
    }

    /**
     * @return CarrierService[]
     */
    public function getAll(): array
    {
        return $this->samedayServiceRepository->getServices();
    }

    /**
     * @return CarrierService[]
     */
    public function getAvailableServices(): array
    {
        return $this->samedayServiceRepository->getAvailableServices();
    }

    /**
     * @param int $samedayServiceId
     *
     * @return array
     */
    public function getServiceIdOptionalTaxes(int $samedayServiceId): array
    {
        return $this->samedayServiceRepository->getServiceIdOptionalTaxes($samedayServiceId);
    }

    /**
     * @param CourierServiceDto $service
     *
     * @return void
     */
    public function add(CourierServiceDto $service): void
    {
        $this->samedayServiceRepository->addService($service);
    }

    /**
     * @param CourierServiceDto $service
     * @param int $localId
     *
     * @return bool
     */
    public function updateFromRemote(CourierServiceDto $service, int $localId): bool
    {
        return $this->samedayServiceRepository->updateServiceCode($service, $localId);
    }

    /**
     * @param int $id
     * @param array $fields
     *
     * @return bool
     */
    public function updateFields(int $id, array $fields): bool
    {
        $fields['id'] = $id;

        return $this->samedayServiceRepository->updateService($fields);
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteById(int $id): void
    {
        $this->samedayServiceRepository->deleteService($id);
    }
}
