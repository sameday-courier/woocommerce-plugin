<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class DeletePickupPointRequest
{
    private DeletePickupPointItem $deletePickupPointItem;

    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @param DeletePickupPointItem $deletePickupPointItem
     * @param CourierServiceProviderInterface $courierServiceProvider
     */
    public function __construct(
        DeletePickupPointItem $deletePickupPointItem,
        CourierServiceProviderInterface $courierServiceProvider
    ) {
        $this->deletePickupPointItem = $deletePickupPointItem;
        $this->courierServiceProvider = $courierServiceProvider;
    }

    /**
     * @return DeletePickupPointItem
     */
    public function getDeletePickupPointItem(): DeletePickupPointItem
    {
        return $this->deletePickupPointItem;
    }

    /**
     * @return CourierServiceProviderInterface
     */
    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }
}
