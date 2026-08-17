<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Domain\Ports\EditServiceServiceProviderInterface;

final class EditServiceRequest
{
    private EditServiceItem $editServiceItem;

    private EditServiceServiceProviderInterface $editServiceServiceProvider;

    /**
     * @param EditServiceItem $editServiceItem
     * @param EditServiceServiceProviderInterface $editServiceServiceProvider
     */
    public function __construct(
        EditServiceItem $editServiceItem,
        EditServiceServiceProviderInterface $editServiceServiceProvider
    ) {
        $this->editServiceItem = $editServiceItem;
        $this->editServiceServiceProvider = $editServiceServiceProvider;
    }

    /**
     * @return EditServiceItem
     */
    public function getEditServiceItem(): EditServiceItem
    {
        return $this->editServiceItem;
    }

    /**
     * @return EditServiceServiceProviderInterface
     */
    public function getEditServiceServiceProvider(): EditServiceServiceProviderInterface
    {
        return $this->editServiceServiceProvider;
    }
}
