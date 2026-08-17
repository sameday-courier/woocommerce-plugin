<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class AddNewPickupPointRequest
{
    private AddNewPickupPointItem $addNewPickupPointItem;

    private CourierServiceProviderInterface $courierServiceProvider;

    public function __construct(
        AddNewPickupPointItem $addNewPickupPointItem,
        CourierServiceProviderInterface $courierServiceProvider
    ) {
        $this->addNewPickupPointItem = $addNewPickupPointItem;
        $this->courierServiceProvider = $courierServiceProvider;
    }

    public function getAddNewPickupPointItem(): AddNewPickupPointItem
    {
        return $this->addNewPickupPointItem;
    }

    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }
}
