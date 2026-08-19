<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PickupPointStoreServiceProviderInterface;

final class RefreshPickupPointRequest
{
    private CourierServiceProviderInterface $courierServiceProvider;

    private PickupPointStoreServiceProviderInterface $pickupPointStore;

    /**
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param PickupPointStoreServiceProviderInterface $pickupPointStore
     */
    public function __construct(
        CourierServiceProviderInterface $courierServiceProvider,
        PickupPointStoreServiceProviderInterface $pickupPointStore
    ) {
        $this->courierServiceProvider = $courierServiceProvider;
        $this->pickupPointStore = $pickupPointStore;
    }

    /**
     * @return CourierServiceProviderInterface
     */
    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }

    /**
     * @return PickupPointStoreServiceProviderInterface
     */
    public function getPickupPointStore(): PickupPointStoreServiceProviderInterface
    {
        return $this->pickupPointStore;
    }
}
