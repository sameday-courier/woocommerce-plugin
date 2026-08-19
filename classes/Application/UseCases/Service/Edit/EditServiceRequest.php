<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Domain\Ports\ServiceCatalogStoreServiceProviderInterface;

final class EditServiceRequest
{
    private EditServiceItem $editServiceItem;

    private ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore;

    /**
     * @param EditServiceItem $editServiceItem
     * @param ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore
     */
    public function __construct(
        EditServiceItem $editServiceItem,
        ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore
    ) {
        $this->editServiceItem = $editServiceItem;
        $this->serviceCatalogStore = $serviceCatalogStore;
    }

    /**
     * @return EditServiceItem
     */
    public function getEditServiceItem(): EditServiceItem
    {
        return $this->editServiceItem;
    }

    /**
     * @return ServiceCatalogStoreServiceProviderInterface
     */
    public function getServiceCatalogStore(): ServiceCatalogStoreServiceProviderInterface
    {
        return $this->serviceCatalogStore;
    }
}
