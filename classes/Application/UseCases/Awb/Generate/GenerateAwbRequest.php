<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Domain\Ports\CarrierShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PickupPointStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostAwbGenerationServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\ServiceCatalogStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;

final class GenerateAwbRequest
{
    private GenerateAwbItem $generateAwbItem;

    private ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore;

    private PickupPointStoreServiceProviderInterface $pickupPointStore;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    private PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider;

    private CarrierShippingHdAddressParserInterface $hdAddressParser;

    private StateCodeResolverInterface $stateCodeResolver;

    private CityPostalCodeProviderInterface $cityPostalCodeProvider;

    public function __construct(
        GenerateAwbItem $generateAwbItem,
        ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore,
        PickupPointStoreServiceProviderInterface $pickupPointStore,
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider,
        PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider,
        CarrierShippingHdAddressParserInterface $hdAddressParser,
        StateCodeResolverInterface $stateCodeResolver,
        CityPostalCodeProviderInterface $cityPostalCodeProvider
    ) {
        $this->generateAwbItem = $generateAwbItem;
        $this->serviceCatalogStore = $serviceCatalogStore;
        $this->pickupPointStore = $pickupPointStore;
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
        $this->postAwbGenerationServiceProvider = $postAwbGenerationServiceProvider;
        $this->hdAddressParser = $hdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
        $this->cityPostalCodeProvider = $cityPostalCodeProvider;
    }

    public function getGenerateAwbItem(): GenerateAwbItem
    {
        return $this->generateAwbItem;
    }

    public function getServiceCatalogStore(): ServiceCatalogStoreServiceProviderInterface
    {
        return $this->serviceCatalogStore;
    }

    public function getPickupPointStore(): PickupPointStoreServiceProviderInterface
    {
        return $this->pickupPointStore;
    }

    public function getOrderAwbStore(): OrderAwbStoreServiceProviderInterface
    {
        return $this->orderAwbStore;
    }

    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }

    public function getPostAwbGenerationServiceProvider(): PostAwbGenerationServiceProviderInterface
    {
        return $this->postAwbGenerationServiceProvider;
    }

    public function getHdAddressParser(): CarrierShippingHdAddressParserInterface
    {
        return $this->hdAddressParser;
    }

    public function getStateCodeResolver(): StateCodeResolverInterface
    {
        return $this->stateCodeResolver;
    }

    public function getCityPostalCodeProvider(): CityPostalCodeProviderInterface
    {
        return $this->cityPostalCodeProvider;
    }
}
