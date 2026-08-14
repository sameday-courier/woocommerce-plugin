<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Sameday\Objects\ParcelDimensionsObject;
use SamedayCourier\Shipping\Application\Common\Factories\BillingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ShippingDtoFactory;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostAwbGenerationServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CarrierShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class GenerateAwbRequest
{
    private GenerateAwbItem $generateAwbItem;

    /**
     * @var CourierServiceProviderInterface $courier
     */
    private CourierServiceProviderInterface $courier;

    private SamedayServiceRepository $samedayServiceRepository;

    private SamedayPickupPointRepository $samedayPickupPointRepository;

    private PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider;

    private CarrierShippingHdAddressParserInterface $samedayShippingHdAddressParser;

    private StateCodeResolverInterface $stateCodeResolver;

    /**
     * @var ParcelDimensionsObject[] $parcelsDimensions
     */
    private array $parcelsDimensions;

    private LockerDtoFactory $lockerDtoFactory;

    private ShippingDtoFactory $shippingDtoFactory;

    private BillingDtoFactory $billingDtoFactory;

    private GenerateAwbValidator $generateAwbValidator;

    private AwbGenerateServiceTaxResolver $awbGenerateServiceTaxResolver;

    private AwbGenerateRecipientResolver $awbGenerateRecipientResolver;

    private CarrierServiceRules $carrierServiceRules;

    private OrderAwbProviderInterface $orderAwbProvider;

    /**
     * @param ParcelDimensionsObject[] $parcelsDimensions
     */
    public function __construct(
        GenerateAwbItem $generateAwbItem,
        CourierServiceProviderInterface $courier,
        SamedayPickupPointRepository $samedayPickupPointRepository,
        SamedayServiceRepository $samedayServiceRepository,
        PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider,
        CarrierShippingHdAddressParserInterface $samedayShippingHdAddressParser,
        StateCodeResolverInterface $stateCodeResolver,
        array $parcelsDimensions,
        LockerDtoFactory $lockerDtoFactory,
        ShippingDtoFactory $shippingDtoFactory,
        BillingDtoFactory $billingDtoFactory,
        GenerateAwbValidator $generateAwbValidator,
        CarrierServiceRules $carrierServiceRules,
        OrderAwbProviderInterface $orderAwbProvider,
        CityPostalCodeProviderInterface $cityPostalCodeProvider
    ) {
        $this->generateAwbItem = $generateAwbItem;
        $this->courier = $courier;
        $this->carrierServiceRules = $carrierServiceRules;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayPickupPointRepository = $samedayPickupPointRepository;
        $this->postAwbGenerationServiceProvider = $postAwbGenerationServiceProvider;
        $this->samedayShippingHdAddressParser = $samedayShippingHdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
        $this->parcelsDimensions = $parcelsDimensions;
        $this->lockerDtoFactory = $lockerDtoFactory;
        $this->shippingDtoFactory = $shippingDtoFactory;
        $this->billingDtoFactory = $billingDtoFactory;
        $this->generateAwbValidator = $generateAwbValidator;
        $this->awbGenerateServiceTaxResolver = new AwbGenerateServiceTaxResolver($samedayServiceRepository);
        $this->awbGenerateRecipientResolver = new AwbGenerateRecipientResolver(
            $carrierServiceRules,
            $samedayShippingHdAddressParser,
            $stateCodeResolver,
            $cityPostalCodeProvider,
        );
        $this->orderAwbProvider = $orderAwbProvider;
    }

    public function getGenerateAwbItem(): GenerateAwbItem
    {
        return $this->generateAwbItem;
    }

    /**
     * @return CourierServiceProviderInterface
     */
    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }

    public function getSamedayServiceRepository(): SamedayServiceRepository
    {
        return $this->samedayServiceRepository;
    }

    public function getSamedayPickupPointRepository(): SamedayPickupPointRepository
    {
        return $this->samedayPickupPointRepository;
    }

    public function getPostAwbGenerationServiceProvider(): PostAwbGenerationServiceProviderInterface
    {
        return $this->postAwbGenerationServiceProvider;
    }

    public function getSamedayShippingHdAddressParser(): CarrierShippingHdAddressParserInterface
    {
        return $this->samedayShippingHdAddressParser;
    }

    public function getStateCodeResolver(): StateCodeResolverInterface
    {
        return $this->stateCodeResolver;
    }

    /**
     * @return ParcelDimensionsObject[]
     */
    public function getParcelsDimensions(): array
    {
        return $this->parcelsDimensions;
    }

    public function getLockerDtoFactory(): LockerDtoFactory
    {
        return $this->lockerDtoFactory;
    }

    public function getShippingDtoFactory(): ShippingDtoFactory
    {
        return $this->shippingDtoFactory;
    }

    public function getBillingDtoFactory(): BillingDtoFactory
    {
        return $this->billingDtoFactory;
    }

    public function getGenerateAwbValidator(): GenerateAwbValidator
    {
        return $this->generateAwbValidator;
    }

    public function getAwbGenerateServiceTaxResolver(): AwbGenerateServiceTaxResolver
    {
        return $this->awbGenerateServiceTaxResolver;
    }

    public function getAwbGenerateRecipientResolver(): AwbGenerateRecipientResolver
    {
        return $this->awbGenerateRecipientResolver;
    }

    public function getCarrierServiceRules(): CarrierServiceRules
    {
        return $this->carrierServiceRules;
    }

    public function getOrderAwbProvider(): OrderAwbProviderInterface
    {
        return $this->orderAwbProvider;
    }
}
