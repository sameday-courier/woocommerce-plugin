<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\DTOs\Requests\OrderShippingChangesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\PostAwbResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CarrierShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingChangesServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PickupPointStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\ServiceCatalogStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateRecipientResolver;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidatorRequest;
use Throwable;

/**
 * @extends AbstractUseCase<GenerateAwbRequest, GenerateAwbResponse>
 *
 * @method GenerateAwbResponse execute(GenerateAwbRequest $request)
 */
final class GenerateAwb extends AbstractUseCase
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
     * @var OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider
     */
    private OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider;

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
     * @param OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider
     * @param CarrierShippingHdAddressParserInterface $hdAddressParser
     * @param StateCodeResolverInterface $stateCodeResolver
     * @param CityPostalCodeProviderInterface $cityPostalCodeProvider
     */
    public function __construct(
        ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore,
        PickupPointStoreServiceProviderInterface $pickupPointStore,
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider,
        OrderShippingChangesServiceProviderInterface $orderShippingChangesServiceProvider,
        CarrierShippingHdAddressParserInterface $hdAddressParser,
        StateCodeResolverInterface $stateCodeResolver,
        CityPostalCodeProviderInterface $cityPostalCodeProvider
    ) {
        $this->serviceCatalogStore = $serviceCatalogStore;
        $this->pickupPointStore = $pickupPointStore;
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
        $this->orderShippingChangesServiceProvider = $orderShippingChangesServiceProvider;
        $this->hdAddressParser = $hdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
        $this->cityPostalCodeProvider = $cityPostalCodeProvider;
    }

    /**
     * @param GenerateAwbRequest $request
     *
     * @return GenerateAwbResponse
     */
    protected function processAction(RequestInterface $request): GenerateAwbResponse
    {
        $packageDimensions = $this->normalizePackageDimensions($request->getPackageDimensions());
        $carrierService = $this->serviceCatalogStore->getBySamedayId($request->getServiceId());
        $pickupPoint = $this->pickupPointStore->getBySamedayId($request->getPickupPointId());
        $shipping = $request->getShipping();
        $billing = $request->getBilling();
        $locker = $request->getLocker();

        $carrierServiceRules = new CarrierServiceRules($this->serviceCatalogStore);
        $awbValidator = (new GenerateAwbValidator())->validate(
            new GenerateAwbValidatorRequest(
                $request->getOrderId(),
                $carrierService,
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
            $carrierService,
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
            $carrierService,
            $locker,
        );

        $awbRequestDto = new PostAwbRequestDto(
            $pickupPoint->getSamedayId(),
            null,
            $request->getPackageType(),
            $packageDimensions,
            $carrierService->getSamedayId(),
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

        return $this->runPostGenerationJob($request, $carrierService, $awb);
    }

    /**
     * @param GenerateAwbRequest $request
     * @param CarrierService $carrierService
     * @param PostAwbResponseDto $awb
     *
     * @return GenerateAwbResponse
     */
    private function runPostGenerationJob(
        GenerateAwbRequest $request,
        CarrierService $carrierService,
        PostAwbResponseDto $awb
    ): GenerateAwbResponse {
        $awbNumber = $awb->getAwbNumber();

        if (
            !$this->orderAwbStore->save(
                $request->getOrderId(),
                $awbNumber,
                $awb->getCost(),
                $awb->getParcels()
            )
        ) {
            try {
                $this->courierServiceProvider->removeAwb(new RemoveAwbRequestDto($awbNumber));

                $message = 'The AWB was generated but could not be saved. So it has been cancelled, please try again.';
            } catch (Throwable $rollbackException) {
                $message = sprintf(
                    'The AWB %s was generated but could not be saved, and the automatic cancellation failed. 
                    Please remove it manually.',
                    $awbNumber
                );
            }

            return new GenerateAwbResponse($message, true);
        }

        $applyOrderChanges = $this->orderShippingChangesServiceProvider->apply(
            new OrderShippingChangesRequestDto(
                $request->getOrderId(),
                $carrierService,
                $request->getShippingLines()
            )
        );

        $message = 'Awb generated successfully.';
        if (!$applyOrderChanges->isSuccess()) {
            $message .= sprintf("but %s", $applyOrderChanges->getMessage());
        }

        return new GenerateAwbResponse(
            $message,
            false
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
