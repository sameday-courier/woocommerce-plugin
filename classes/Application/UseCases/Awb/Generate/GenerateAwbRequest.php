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
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressUpdaterInterface;
use SamedayCourier\Shipping\Domain\Ports\SamedayShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class GenerateAwbRequest
{
    private GenerateAwbItem $generateAwbItem;

    /**
     * @var CourierServiceProviderInterface $courier
     */
    private CourierServiceProviderInterface $courier;

    private DbHandler $dbHandler;

    private SamedayServiceRepository $samedayServiceRepository;

    private SamedayPickupPointRepository $samedayPickupPointRepository;

    private SamedayAwbRepository $samedayAwbRepository;

    private OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater;

    private SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser;

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

    private SamedayServiceRules $samedayServiceRules;

    private OrderAwbProviderInterface $orderAwbProvider;

    /**
     * @param ParcelDimensionsObject[] $parcelsDimensions
     */
    public function __construct(
        GenerateAwbItem $generateAwbItem,
        CourierServiceProviderInterface $courier,
        DbHandler $dbHandler,
        SamedayPickupPointRepository $samedayPickupPointRepository,
        SamedayServiceRepository $samedayServiceRepository,
        SamedayAwbRepository $samedayAwbRepository,
        OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater,
        SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser,
        StateCodeResolverInterface $stateCodeResolver,
        array $parcelsDimensions,
        LockerDtoFactory $lockerDtoFactory,
        ShippingDtoFactory $shippingDtoFactory,
        BillingDtoFactory $billingDtoFactory,
        GenerateAwbValidator $generateAwbValidator,
        SamedayServiceRules $samedayServiceRules,
        OrderAwbProviderInterface $orderAwbProvider,
        CityPostalCodeProviderInterface $cityPostalCodeProvider
    ) {
        $this->generateAwbItem = $generateAwbItem;
        $this->courier = $courier;
        $this->dbHandler = $dbHandler;
        $this->samedayServiceRules = $samedayServiceRules;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayPickupPointRepository = $samedayPickupPointRepository;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->orderShippingAddressUpdater = $orderShippingAddressUpdater;
        $this->samedayShippingHdAddressParser = $samedayShippingHdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
        $this->parcelsDimensions = $parcelsDimensions;
        $this->lockerDtoFactory = $lockerDtoFactory;
        $this->shippingDtoFactory = $shippingDtoFactory;
        $this->billingDtoFactory = $billingDtoFactory;
        $this->generateAwbValidator = $generateAwbValidator;
        $this->awbGenerateServiceTaxResolver = new AwbGenerateServiceTaxResolver($samedayServiceRepository);
        $this->awbGenerateRecipientResolver = new AwbGenerateRecipientResolver(
            $samedayServiceRules,
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

    public function getDbHandler(): DbHandler
    {
        return $this->dbHandler;
    }

    public function getSamedayServiceRepository(): SamedayServiceRepository
    {
        return $this->samedayServiceRepository;
    }

    public function getSamedayPickupPointRepository(): SamedayPickupPointRepository
    {
        return $this->samedayPickupPointRepository;
    }

    public function getSamedayAwbRepository(): SamedayAwbRepository
    {
        return $this->samedayAwbRepository;
    }

    public function getOrderShippingAddressUpdater(): OrderShippingAddressUpdaterInterface
    {
        return $this->orderShippingAddressUpdater;
    }

    public function getSamedayShippingHdAddressParser(): SamedayShippingHdAddressParserInterface
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

    public function getSamedayServiceRules(): SamedayServiceRules
    {
        return $this->samedayServiceRules;
    }

    public function getOrderAwbProvider(): OrderAwbProviderInterface
    {
        return $this->orderAwbProvider;
    }
}
