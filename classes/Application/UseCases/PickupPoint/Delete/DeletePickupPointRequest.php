<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class DeletePickupPointRequest
{
    private DeletePickupPointItem $deletePickupPointItem;

    private CourierServiceProviderInterface $courier;

    public function __construct(
        DeletePickupPointItem $deletePickupPointItem,
        CourierServiceProviderInterface $courier
    ) {
        $this->deletePickupPointItem = $deletePickupPointItem;
        $this->courier = $courier;
    }

    public function getDeletePickupPointItem(): DeletePickupPointItem
    {
        return $this->deletePickupPointItem;
    }

    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }
}
