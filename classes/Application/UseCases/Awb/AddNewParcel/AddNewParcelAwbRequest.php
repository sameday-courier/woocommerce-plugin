<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;

final class AddNewParcelAwbRequest
{
    private AddNewParcelAwbItem $awbItem;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    public function __construct(
        AddNewParcelAwbItem $awbItem,
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider
    ) {
        $this->awbItem = $awbItem;
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
    }

    public function getAwbItem(): AddNewParcelAwbItem
    {
        return $this->awbItem;
    }

    public function getOrderAwbStore(): OrderAwbStoreServiceProviderInterface
    {
        return $this->orderAwbStore;
    }

    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }
}
