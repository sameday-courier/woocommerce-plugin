<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;

final class ShowAsPdfAwbRequest
{
    private ShowAsPdfAwbItem $showAsPdfAwbItem;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    /**
     * @param ShowAsPdfAwbItem $showAsPdfAwbItem
     * @param OrderAwbStoreServiceProviderInterface $orderAwbStore
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param CarrierSettingsProviderInterface $carrierSettingsProvider
     */
    public function __construct(
        ShowAsPdfAwbItem $showAsPdfAwbItem,
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider,
        CarrierSettingsProviderInterface $carrierSettingsProvider
    ) {
        $this->showAsPdfAwbItem = $showAsPdfAwbItem;
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
        $this->carrierSettingsProvider = $carrierSettingsProvider;
    }

    /**
     * @return ShowAsPdfAwbItem
     */
    public function getShowAsPdfAwbItem(): ShowAsPdfAwbItem
    {
        return $this->showAsPdfAwbItem;
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
     * @return CarrierSettingsProviderInterface
     */
    public function getCarrierSettingsProvider(): CarrierSettingsProviderInterface
    {
        return $this->carrierSettingsProvider;
    }
}
