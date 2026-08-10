<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Factories\BillingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ShippingDtoFactory;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\Common\Services\AwbRemover;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressUpdaterInterface;
use SamedayCourier\Shipping\Domain\Ports\SamedayShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbRequest
{
    private GenerateAwbItem $generateAwbItem;

    private Sameday $sameday;

    private DbHandler $dbHandler;

    private SamedayServiceRepository $samedayServiceRepository;

    private SamedayPickupPointRepository $samedayPickupPointRepository;

    private SamedayAwbRepository $samedayAwbRepository;

    private OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater;

    private AwbErrorParser $awbErrorParser;

    private SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser;

    private StateCodeResolverInterface $stateCodeResolver;

    private ParcelDimensionsFactory $parcelDimensionsFactory;

    private LockerDtoFactory $lockerDtoFactory;

    private ShippingDtoFactory $shippingDtoFactory;

    private BillingDtoFactory $billingDtoFactory;

    private GenerateAwbValidator $generateAwbValidator;

    private AwbGenerateServiceTaxResolver $awbGenerateServiceTaxResolver;

    private AwbGenerateRecipientResolver $awbGenerateRecipientResolver;

    private SamedayServiceRules $samedayServiceRules;

    private AwbRemover $awbRemover;

    public function __construct(
        GenerateAwbItem $generateAwbItem,
        Sameday $sameday,
        DbHandler $dbHandler,
        SamedayServiceRepository $samedayServiceRepository,
        SamedayPickupPointRepository $samedayPickupPointRepository,
        SamedayAwbRepository $samedayAwbRepository,
        OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater,
        AwbErrorParser $awbErrorParser,
        SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser,
        StateCodeResolverInterface $stateCodeResolver,
        ParcelDimensionsFactory $parcelDimensionsFactory,
        LockerDtoFactory $lockerDtoFactory,
        ShippingDtoFactory $shippingDtoFactory,
        BillingDtoFactory $billingDtoFactory,
        GenerateAwbValidator $generateAwbValidator,
        AwbGenerateServiceTaxResolver $awbGenerateServiceTaxResolver,
        SamedayServiceRules $samedayServiceRules,
        AwbRemover $awbRemover
    ) {
        $this->generateAwbItem = $generateAwbItem;
        $this->sameday = $sameday;
        $this->dbHandler = $dbHandler;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayPickupPointRepository = $samedayPickupPointRepository;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->orderShippingAddressUpdater = $orderShippingAddressUpdater;
        $this->awbErrorParser = $awbErrorParser;
        $this->samedayShippingHdAddressParser = $samedayShippingHdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
        $this->parcelDimensionsFactory = $parcelDimensionsFactory;
        $this->lockerDtoFactory = $lockerDtoFactory;
        $this->shippingDtoFactory = $shippingDtoFactory;
        $this->billingDtoFactory = $billingDtoFactory;
        $this->generateAwbValidator = $generateAwbValidator;
        $this->awbGenerateServiceTaxResolver = $awbGenerateServiceTaxResolver;
        $this->samedayServiceRules = $samedayServiceRules;
        $this->awbGenerateRecipientResolver = new AwbGenerateRecipientResolver(
            $samedayServiceRules,
            $samedayShippingHdAddressParser,
            $stateCodeResolver,
        );
        $this->awbRemover = $awbRemover;
    }

    public function getGenerateAwbItem(): GenerateAwbItem
    {
        return $this->generateAwbItem;
    }

    public function getSameday(): Sameday
    {
        return $this->sameday;
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

    public function getAwbErrorParser(): AwbErrorParser
    {
        return $this->awbErrorParser;
    }

    public function getSamedayShippingHdAddressParser(): SamedayShippingHdAddressParserInterface
    {
        return $this->samedayShippingHdAddressParser;
    }

    public function getStateCodeResolver(): StateCodeResolverInterface
    {
        return $this->stateCodeResolver;
    }

    public function getParcelDimensionsFactory(): ParcelDimensionsFactory
    {
        return $this->parcelDimensionsFactory;
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

    public function getAwbRemover(): AwbRemover
    {
        return $this->awbRemover;
    }
}
