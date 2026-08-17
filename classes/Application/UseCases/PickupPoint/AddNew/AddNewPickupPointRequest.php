<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use SamedayCourier\Shipping\Domain\Ports\AddNewPickupPointServiceProviderInterface;

final class AddNewPickupPointRequest
{
    private AddNewPickupPointItem $addNewPickupPointItem;

    private AddNewPickupPointServiceProviderInterface $addNewPickupPointServiceProvider;

    public function __construct(
        AddNewPickupPointItem $addNewPickupPointItem,
        AddNewPickupPointServiceProviderInterface $addNewPickupPointServiceProvider
    ) {
        $this->addNewPickupPointItem = $addNewPickupPointItem;
        $this->addNewPickupPointServiceProvider = $addNewPickupPointServiceProvider;
    }

    public function getAddNewPickupPointItem(): AddNewPickupPointItem
    {
        return $this->addNewPickupPointItem;
    }

    public function getAddNewPickupPointServiceProvider(): AddNewPickupPointServiceProviderInterface
    {
        return $this->addNewPickupPointServiceProvider;
    }
}
