<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PackageHistoryStoreServiceProviderInterface;

final class ShowHistoryAwbRequest
{
    private ShowHistoryAwbItem $showHistoryAwbItem;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    private PackageHistoryStoreServiceProviderInterface $packageHistoryStore;

    /**
     * @param ShowHistoryAwbItem $showHistoryAwbItem
     * @param OrderAwbStoreServiceProviderInterface $orderAwbStore
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param PackageHistoryStoreServiceProviderInterface $packageHistoryStore
     */
    public function __construct(
        ShowHistoryAwbItem $showHistoryAwbItem,
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider,
        PackageHistoryStoreServiceProviderInterface $packageHistoryStore
    ) {
        $this->showHistoryAwbItem = $showHistoryAwbItem;
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
        $this->packageHistoryStore = $packageHistoryStore;
    }

    /**
     * @return ShowHistoryAwbItem
     */
    public function getShowHistoryAwbItem(): ShowHistoryAwbItem
    {
        return $this->showHistoryAwbItem;
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
     * @return PackageHistoryStoreServiceProviderInterface
     */
    public function getPackageHistoryStore(): PackageHistoryStoreServiceProviderInterface
    {
        return $this->packageHistoryStore;
    }
}
