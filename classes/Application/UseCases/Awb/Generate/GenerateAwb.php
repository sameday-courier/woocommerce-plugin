<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Common\Factories\BillingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ShippingDtoFactory;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostAwbGenerationRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostAwbRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CarrierShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PickupPointStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostAwbGenerationServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\ServiceCatalogStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidatorRequest;

final class GenerateAwb
{
    /**
     * @var ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore
     */
    private ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore;

    /**
     * @var PickupPointStoreServiceProviderInterface $pickupPointStore
     */
    private PickupPointStoreServiceProviderInterface $pickupPointStore;

    /**
     * @var OrderAwbStoreServiceProviderInterface $orderAwbStore
     */
    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    /**
     * @var CourierServiceProviderInterface $courierServiceProvider
     */
    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @var PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider
     */
    private PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider;

    /**
     * @var CarrierShippingHdAddressParserInterface $hdAddressParser
     */
    private CarrierShippingHdAddressParserInterface $hdAddressParser;

    /**
     * @var StateCodeResolverInterface $stateCodeResolver
     */
    private StateCodeResolverInterface $stateCodeResolver;

    /**
     * @var CityPostalCodeProviderInterface $cityPostalCodeProvider
     */
    private CityPostalCodeProviderInterface $cityPostalCodeProvider;

    /**
     * @param ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore
     * @param PickupPointStoreServiceProviderInterface $pickupPointStore
     * @param OrderAwbStoreServiceProviderInterface $orderAwbStore
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider
     * @param CarrierShippingHdAddressParserInterface $hdAddressParser
     * @param StateCodeResolverInterface $stateCodeResolver
     * @param CityPostalCodeProviderInterface $cityPostalCodeProvider
     */
    public function __construct(
        ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore,
        PickupPointStoreServiceProviderInterface $pickupPointStore,
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider,
        PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider,
        CarrierShippingHdAddressParserInterface $hdAddressParser,
        StateCodeResolverInterface $stateCodeResolver,
        CityPostalCodeProviderInterface $cityPostalCodeProvider
    ) {
        $this->serviceCatalogStore = $serviceCatalogStore;
        $this->pickupPointStore = $pickupPointStore;
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
        $this->postAwbGenerationServiceProvider = $postAwbGenerationServiceProvider;
        $this->hdAddressParser = $hdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
        $this->cityPostalCodeProvider = $cityPostalCodeProvider;
    }

    /**
     * @param GenerateAwbRequest $request
     *
     * @return GenerateAwbResponse
     */
    public function execute(GenerateAwbRequest $request): GenerateAwbResponse
    {
        $packageDimensions = $this->normalizePackageDimensions($request->getPackageDimensions());
        $service = $this->serviceCatalogStore->getBySamedayId($request->getServiceId());
        $pickupPoint = $this->pickupPointStore->getBySamedayId($request->getPickupPointId());
        $shipping = (new ShippingDtoFactory())->fromInput($request->getShipping());
        $billing = (new BillingDtoFactory())->fromInput($request->getBilling());
        $locker = (new LockerDtoFactory())->fromInput($request->getLocker());

        $carrierServiceRules = new CarrierServiceRules($this->serviceCatalogStore);
        $awbValidator = (new GenerateAwbValidator())->validate(
            new GenerateAwbValidatorRequest(
                $request->getOrderId(),
                $service,
                $pickupPoint,
                AwbGenerateRecipientResolver::resolveDestinationCountry($shipping, $billing),
                AwbGenerateRecipientResolver::resolvePhone($shipping, $billing),
                AwbGenerateRecipientResolver::resolveEmail($shipping, $billing),
                $request->getShippingLines(),
                null !== $this->orderAwbStore->getByOrderId($request->getOrderId()),
                [] !== $packageDimensions,
            )
        );

        if ($awbValidator->hasErrors()) {
            return new GenerateAwbResponse(
                $awbValidator->toString(),
                true
            );
        }

        $serviceTax = (new AwbGenerateServiceTaxResolver($this->serviceCatalogStore))->resolve(
            $service,
            $request->hasOpenPackage(),
            $request->hasLockerFirstMile(),
            $request->getPackageType()
        );

        $awbRecipient = (new AwbGenerateRecipientResolver(
            $carrierServiceRules,
            $this->hdAddressParser,
            $this->stateCodeResolver,
            $this->cityPostalCodeProvider,
        ))->resolve(
            $request->getOrderId(),
            $shipping,
            $billing,
            $service,
            $locker,
        );

        $awbRequestDto = new PostAwbRequestDto(
            $pickupPoint->getSamedayId(),
            null,
            $request->getPackageType(),
            $packageDimensions,
            $service->getSamedayId(),
            $request->getAwbPayment(),
            $awbRecipient->getRecipient(),
            $request->getInsuranceValue(),
            $request->getRepayment(),
            CarrierConstants::COD_COLLECTOR_CLIENT,
            null,
            $serviceTax->getServiceTaxIds(),
            null,
            $request->getClientReference(),
            $request->getObservation(),
            '',
            '',
            null,
            $awbRecipient->getOoh()->getLockerId(),
            null,
            $awbRecipient->getOoh()->getOohLastMile(),
            $awbRecipient->getCurrency()
        );

        try {
            $awb = $this->courierServiceProvider->postAwb($awbRequestDto);
        } catch (CourierServiceException $exception) {
            return new GenerateAwbResponse(
                $exception->getMessage(),
                true
            );
        }

        $postAwbGenerationResponse = $this->postAwbGenerationServiceProvider->apply(
            new PostAwbGenerationRequestDto(
                $request->getOrderId(),
                $request->getShippingLines(),
                $service,
                $awb->getAwbNumber(),
                $awb->getCost(),
                $awb->getParcels()
            ),
            $this->courierServiceProvider
        );

        return new GenerateAwbResponse(
            $postAwbGenerationResponse->getMessage(),
            !$postAwbGenerationResponse->isSuccess()
        );
    }

    /**
     * @param array $packageDimensions
     *
     * @return array
     */
    private function normalizePackageDimensions(array $packageDimensions): array
    {
        ksort($packageDimensions);

        $result = [];
        foreach ($packageDimensions as $parcel) {
            if (!is_array($parcel)) {
                continue;
            }

            $result[] = $parcel;
        }

        return $result;
    }
}
