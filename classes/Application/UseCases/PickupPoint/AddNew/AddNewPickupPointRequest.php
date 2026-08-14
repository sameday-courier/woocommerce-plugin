<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class AddNewPickupPointRequest
{
    private AddNewPickupPointItem $addNewPickupPointItem;

    private CourierServiceProviderInterface $courier;

    public function __construct(
        AddNewPickupPointItem $addNewPickupPointItem,
        CourierServiceProviderInterface $courier
    ) {
        $this->addNewPickupPointItem = $addNewPickupPointItem;
        $this->courier = $courier;
    }

    public function getAddNewPickupPointItem(): AddNewPickupPointItem
    {
        return $this->addNewPickupPointItem;
    }

    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }
}
