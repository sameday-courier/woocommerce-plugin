<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostRemoveAwbServiceProviderInterface;

final class RemoveAwbRequest
{
    private RemoveAwbItem $removeAwbItem;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    private PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider;

    public function __construct(
        RemoveAwbItem $removeAwbItem,
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider,
        PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider
    ) {
        $this->removeAwbItem = $removeAwbItem;
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
        $this->postRemoveAwbServiceProvider = $postRemoveAwbServiceProvider;
    }

    public function getRemoveAwbItem(): RemoveAwbItem
    {
        return $this->removeAwbItem;
    }

    public function getOrderAwbStore(): OrderAwbStoreServiceProviderInterface
    {
        return $this->orderAwbStore;
    }

    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }

    public function getPostRemoveAwbServiceProvider(): PostRemoveAwbServiceProviderInterface
    {
        return $this->postRemoveAwbServiceProvider;
    }
}
