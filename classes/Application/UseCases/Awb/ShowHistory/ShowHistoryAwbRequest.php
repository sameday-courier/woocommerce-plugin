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

    public function getShowHistoryAwbItem(): ShowHistoryAwbItem
    {
        return $this->showHistoryAwbItem;
    }

    public function getOrderAwbStore(): OrderAwbStoreServiceProviderInterface
    {
        return $this->orderAwbStore;
    }

    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }

    public function getPackageHistoryStore(): PackageHistoryStoreServiceProviderInterface
    {
        return $this->packageHistoryStore;
    }
}
