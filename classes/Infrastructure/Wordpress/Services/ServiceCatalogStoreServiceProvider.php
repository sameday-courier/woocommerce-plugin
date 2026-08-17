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

    public function __construct(?SamedayServiceRepository $samedayServiceRepository = null)
    {
        $this->samedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository();
    }

    public function getById(int $id): ?CarrierService
    {
        return $this->samedayServiceRepository->getServiceById($id);
    }

    public function getBySamedayId(int $samedayId): ?CarrierService
    {
        return $this->samedayServiceRepository->getServiceSameday($samedayId);
    }

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
     * @inheritDoc
     */
    public function getServiceIdOptionalTaxes(int $samedayServiceId): array
    {
        return $this->samedayServiceRepository->getServiceIdOptionalTaxes($samedayServiceId);
    }

    public function add(CourierServiceDto $service): void
    {
        $this->samedayServiceRepository->addService($service);
    }

    public function updateFromRemote(CourierServiceDto $service, int $localId): bool
    {
        return $this->samedayServiceRepository->updateServiceCode($service, $localId);
    }

    /**
     * @param array<string, mixed> $fields
     */
    public function updateFields(int $id, array $fields): bool
    {
        $fields['id'] = $id;

        return $this->samedayServiceRepository->updateService($fields);
    }

    public function deleteById(int $id): void
    {
        $this->samedayServiceRepository->deleteService($id);
    }
}
