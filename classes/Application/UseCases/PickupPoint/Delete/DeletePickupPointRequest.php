<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Domain\Ports\DeletePickupPointServiceProviderInterface;

final class DeletePickupPointRequest
{
    private DeletePickupPointItem $deletePickupPointItem;

    private DeletePickupPointServiceProviderInterface $deletePickupPointServiceProvider;

    public function __construct(
        DeletePickupPointItem $deletePickupPointItem,
        DeletePickupPointServiceProviderInterface $deletePickupPointServiceProvider
    ) {
        $this->deletePickupPointItem = $deletePickupPointItem;
        $this->deletePickupPointServiceProvider = $deletePickupPointServiceProvider;
    }

    public function getDeletePickupPointItem(): DeletePickupPointItem
    {
        return $this->deletePickupPointItem;
    }

    public function getDeletePickupPointServiceProvider(): DeletePickupPointServiceProviderInterface
    {
        return $this->deletePickupPointServiceProvider;
    }
}
