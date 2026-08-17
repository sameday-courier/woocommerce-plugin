<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\PackageType;
use SamedayCourier\Shipping\Application\Common\Factories\BillingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ShippingDtoFactory;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GenerateAwbServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostAwbGenerationRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GenerateAwbServiceResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CarrierShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\GenerateAwbServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostAwbGenerationServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidatorRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderAwbProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressArchive;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSamedayShippingHdAddressParser;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Woo\WooOrderAddressRepository;

final class GenerateAwbServiceProvider implements GenerateAwbServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    private SamedayServiceRepository $samedayServiceRepository;

    private SamedayPickupPointRepository $samedayPickupPointRepository;

    private PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider;

    private ParcelDimensionsFactory $parcelDimensionsFactory;

    private LockerDtoFactory $lockerDtoFactory;

    private ShippingDtoFactory $shippingDtoFactory;

    private BillingDtoFactory $billingDtoFactory;

    private GenerateAwbValidator $generateAwbValidator;

    private AwbGenerateServiceTaxResolver $awbGenerateServiceTaxResolver;

    private AwbGenerateRecipientResolver $awbGenerateRecipientResolver;

    private CarrierServiceRules $carrierServiceRules;

    private OrderAwbProviderInterface $orderAwbProvider;

    public function __construct(
        ?CourierServiceProviderInterface $courier = null,
        ?SamedayPickupPointRepository $samedayPickupPointRepository = null,
        ?SamedayServiceRepository $samedayServiceRepository = null,
        ?SamedayAwbRepository $samedayAwbRepository = null,
        ?SamedayCityRepository $samedayCityRepository = null,
        ?SamedayLockerRepository $samedayLockerRepository = null,
        ?PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider = null,
        ?ParcelDimensionsFactory $parcelDimensionsFactory = null,
        ?LockerDtoFactory $lockerDtoFactory = null,
        ?ShippingDtoFactory $shippingDtoFactory = null,
        ?BillingDtoFactory $billingDtoFactory = null,
        ?GenerateAwbValidator $generateAwbValidator = null,
        ?CarrierServiceRules $carrierServiceRules = null,
        ?OrderAwbProviderInterface $orderAwbProvider = null,
        ?CarrierShippingHdAddressParserInterface $samedayShippingHdAddressParser = null,
        ?StateCodeResolverInterface $stateCodeResolver = null,
        ?WooCountriesHandler $wooCountriesHandler = null,
        ?CityPostalCodeProviderInterface $cityPostalCodeProvider = null
    ) {
        $dbHandler = new DbHandler();
        $hdAddressParser = $samedayShippingHdAddressParser ?? new WooSamedayShippingHdAddressParser();
        $resolvedWooCountriesHandler = $wooCountriesHandler ?? new WooCountriesHandler();
        $resolvedStateCodeResolver = $stateCodeResolver ?? new WooStateCodeResolver($resolvedWooCountriesHandler);
        $resolvedSamedayLockerRepository = $samedayLockerRepository ?? new SamedayLockerRepository($dbHandler);
        $resolvedLockerDtoFactory = $lockerDtoFactory ?? new LockerDtoFactory($resolvedSamedayLockerRepository);
        $resolvedSamedayAwbRepository = $samedayAwbRepository ?? new SamedayAwbRepository($dbHandler);
        $resolvedSamedayServiceRepository = $samedayServiceRepository ?? new SamedayServiceRepository($dbHandler);
        $resolvedSamedayCityRepository = $samedayCityRepository ?? new SamedayCityRepository($dbHandler);
        $resolvedCarrierServiceRules = $carrierServiceRules ?? new CarrierServiceRules($resolvedSamedayServiceRepository);

        $this->courier = $courier ?? new CourierServiceProvider();
        $this->samedayPickupPointRepository = $samedayPickupPointRepository ?? new SamedayPickupPointRepository($dbHandler);
        $this->samedayServiceRepository = $resolvedSamedayServiceRepository;
        $this->postAwbGenerationServiceProvider = $postAwbGenerationServiceProvider ?? new PostAwbGenerationServiceProvider(
            $dbHandler,
            new WooOrderShippingAddressUpdater(
                new WooOrderAddressRepository($dbHandler),
                new WooOrderShippingAddressArchive(),
                $resolvedLockerDtoFactory,
                $hdAddressParser,
                $resolvedStateCodeResolver,
            ),
            $resolvedSamedayAwbRepository
        );
        $this->parcelDimensionsFactory = $parcelDimensionsFactory ?? new ParcelDimensionsFactory();
        $this->lockerDtoFactory = $resolvedLockerDtoFactory;
        $this->shippingDtoFactory = $shippingDtoFactory ?? new ShippingDtoFactory();
        $this->billingDtoFactory = $billingDtoFactory ?? new BillingDtoFactory();
        $this->generateAwbValidator = $generateAwbValidator ?? new GenerateAwbValidator();
        $this->carrierServiceRules = $resolvedCarrierServiceRules;
        $this->awbGenerateServiceTaxResolver = new AwbGenerateServiceTaxResolver($resolvedSamedayServiceRepository);
        $this->awbGenerateRecipientResolver = new AwbGenerateRecipientResolver(
            $resolvedCarrierServiceRules,
            $hdAddressParser,
            $resolvedStateCodeResolver,
            $cityPostalCodeProvider ?? $resolvedSamedayCityRepository,
        );
        $this->orderAwbProvider = $orderAwbProvider ?? new WooOrderAwbProvider($resolvedSamedayAwbRepository);
    }

    /**
     * @param GenerateAwbServiceRequestDto $generateAwbServiceRequestDto
     *
     * @return GenerateAwbServiceResponseDto
     */
    public function generate(GenerateAwbServiceRequestDto $generateAwbServiceRequestDto): GenerateAwbServiceResponseDto
    {
        $parcelsDimensions = $this->parcelDimensionsFactory->fromList(
            $generateAwbServiceRequestDto->getPackageDimensions()
        );

        $service = $this->samedayServiceRepository->getServiceSameday($generateAwbServiceRequestDto->getServiceId());
        $pickupPoint = $this->samedayPickupPointRepository->getPickupPointSameday(
            $generateAwbServiceRequestDto->getPickupPointId()
        );
        $shipping = $this->shippingDtoFactory->fromInput($generateAwbServiceRequestDto->getShipping());
        $billing = $this->billingDtoFactory->fromInput($generateAwbServiceRequestDto->getBilling());
        $locker = $this->lockerDtoFactory->fromInput($generateAwbServiceRequestDto->getLocker());

        $awbValidator = $this->generateAwbValidator->validate(
            new GenerateAwbValidatorRequest(
                $generateAwbServiceRequestDto->getOrderId(),
                $service,
                $pickupPoint,
                $billing,
                $generateAwbServiceRequestDto->getShippingLines(),
                null !== $this->orderAwbProvider->get($generateAwbServiceRequestDto->getOrderId()),
                [] !== $parcelsDimensions,
            )
        );

        if ($awbValidator->hasErrors()) {
            return new GenerateAwbServiceResponseDto(
                false,
                $awbValidator->toString()
            );
        }

        $serviceTax = $this->awbGenerateServiceTaxResolver->resolve(
            $service,
            $generateAwbServiceRequestDto->hasOpenPackage(),
            $generateAwbServiceRequestDto->hasLockerFirstMile(),
            $generateAwbServiceRequestDto->getPackageType()
        );

        $awbRecipient = $this->awbGenerateRecipientResolver->resolve(
            $generateAwbServiceRequestDto->getOrderId(),
            $shipping,
            $billing,
            $service,
            $locker,
        );

        $recipient = $awbRecipient->getRecipient();

        $request = new PostAwbRequestDto(
            $pickupPoint->getSamedayId(),
            null,
            new PackageType($generateAwbServiceRequestDto->getPackageType()),
            $parcelsDimensions,
            $service->getSamedayId(),
            new AwbPaymentType($generateAwbServiceRequestDto->getAwbPayment()),
            new AwbRecipientEntityObject(
                $recipient->getCity(),
                $recipient->getCounty(),
                $recipient->getAddress(),
                $recipient->getName(),
                $recipient->getPhone(),
                $recipient->getEmail(),
                $recipient->getCompany(),
                $recipient->getPostcode(),
            ),
            $generateAwbServiceRequestDto->getInsuranceValue(),
            $generateAwbServiceRequestDto->getRepayment(),
            new CodCollectorType(CodCollectorType::CLIENT),
            null,
            $serviceTax->getServiceTaxIds(),
            null,
            $generateAwbServiceRequestDto->getClientReference(),
            $generateAwbServiceRequestDto->getObservation(),
            '',
            '',
            null,
            $awbRecipient->getOoh()->getLockerId(),
            null,
            $awbRecipient->getOoh()->getOohLastMile(),
            $awbRecipient->getCurrency()
        );

        try {
            $awb = $this->courier->postAwb($request);
        } catch (CourierServiceException $exception) {
            return new GenerateAwbServiceResponseDto(
                false,
                $exception->getMessage()
            );
        }

        $postAwbGenerationResponse = $this->postAwbGenerationServiceProvider->apply(
            new PostAwbGenerationRequestDto(
                $generateAwbServiceRequestDto->getOrderId(),
                $generateAwbServiceRequestDto->getShippingLines(),
                $service,
                $awb->getAwbNumber(),
                $awb->getCost(),
                $awb->getParcels()
            ),
            $this->carrierServiceRules,
            $this->courier
        );

        return new GenerateAwbServiceResponseDto(
            $postAwbGenerationResponse->isSuccess(),
            $postAwbGenerationResponse->getMessage()
        );
    }
}
