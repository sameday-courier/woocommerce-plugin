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

    /**
     * @param RemoveAwbItem $removeAwbItem
     * @param OrderAwbStoreServiceProviderInterface $orderAwbStore
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider
     */
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

    /**
     * @return RemoveAwbItem
     */
    public function getRemoveAwbItem(): RemoveAwbItem
    {
        return $this->removeAwbItem;
    }

    /**
     * @return OrderAwbStoreServiceProviderInterface
     */
    public function getOrderAwbStore(): OrderAwbStoreServiceProviderInterface
    {
        return $this->orderAwbStore;
    }

    /**
     * @return CourierServiceProviderInterface
     */
    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }

    /**
     * @return PostRemoveAwbServiceProviderInterface
     */
    public function getPostRemoveAwbServiceProvider(): PostRemoveAwbServiceProviderInterface
    {
        return $this->postRemoveAwbServiceProvider;
    }
}
