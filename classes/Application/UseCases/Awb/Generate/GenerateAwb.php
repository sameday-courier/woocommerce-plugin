<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Common\Factories\BillingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ShippingDtoFactory;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
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
    private GenerateAwbItem $awbItem;

    private ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore;

    private PickupPointStoreServiceProviderInterface $pickupPointStore;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    private PostAwbGenerationServiceProviderInterface $postAwbGenerationServiceProvider;

    private CarrierShippingHdAddressParserInterface $hdAddressParser;

    private StateCodeResolverInterface $stateCodeResolver;

    private CityPostalCodeProviderInterface $cityPostalCodeProvider;

    /**
     * @param GenerateAwbRequest $generateAwbRequest
     */
    public function __construct(GenerateAwbRequest $generateAwbRequest)
    {
        $this->awbItem = $generateAwbRequest->getGenerateAwbItem();
        $this->serviceCatalogStore = $generateAwbRequest->getServiceCatalogStore();
        $this->pickupPointStore = $generateAwbRequest->getPickupPointStore();
        $this->orderAwbStore = $generateAwbRequest->getOrderAwbStore();
        $this->courierServiceProvider = $generateAwbRequest->getCourierServiceProvider();
        $this->postAwbGenerationServiceProvider = $generateAwbRequest->getPostAwbGenerationServiceProvider();
        $this->hdAddressParser = $generateAwbRequest->getHdAddressParser();
        $this->stateCodeResolver = $generateAwbRequest->getStateCodeResolver();
        $this->cityPostalCodeProvider = $generateAwbRequest->getCityPostalCodeProvider();
    }

    /**
     * @return GenerateAwbResponse
     */
    public function execute(): GenerateAwbResponse
    {
        $packageDimensions = $this->normalizePackageDimensions($this->awbItem->getPackageDimensions());
        $service = $this->serviceCatalogStore->getBySamedayId($this->awbItem->getServiceId());
        $pickupPoint = $this->pickupPointStore->getBySamedayId($this->awbItem->getPickupPointId());
        $shipping = (new ShippingDtoFactory())->fromInput($this->awbItem->getShipping());
        $billing = (new BillingDtoFactory())->fromInput($this->awbItem->getBilling());
        $locker = (new LockerDtoFactory())->fromInput($this->awbItem->getLocker());

        $carrierServiceRules = new CarrierServiceRules($this->serviceCatalogStore);
        $awbValidator = (new GenerateAwbValidator())->validate(
            new GenerateAwbValidatorRequest(
                $this->awbItem->getOrderId(),
                $service,
                $pickupPoint,
                $billing,
                $this->awbItem->getShippingLines(),
                null !== $this->orderAwbStore->getByOrderId($this->awbItem->getOrderId()),
                [] !== $packageDimensions,
            )
        );

        if ($awbValidator->hasErrors()) {
            return new GenerateAwbResponse(
                $awbValidator->toString(),
                ResponseNoticeType::ERROR
            );
        }

        $serviceTax = (new AwbGenerateServiceTaxResolver($this->serviceCatalogStore))->resolve(
            $service,
            $this->awbItem->hasOpenPackage(),
            $this->awbItem->hasLockerFirstMile(),
            $this->awbItem->getPackageType()
        );

        $awbRecipient = (new AwbGenerateRecipientResolver(
            $carrierServiceRules,
            $this->hdAddressParser,
            $this->stateCodeResolver,
            $this->cityPostalCodeProvider,
        ))->resolve(
            $this->awbItem->getOrderId(),
            $shipping,
            $billing,
            $service,
            $locker,
        );

        try {
            $awb = $this->courierServiceProvider->postAwb(
                new PostAwbRequestDto(
                    $pickupPoint->getSamedayId(),
                    null,
                    $this->awbItem->getPackageType(),
                    $packageDimensions,
                    $service->getSamedayId(),
                    $this->awbItem->getAwbPayment(),
                    $awbRecipient->getRecipient(),
                    $this->awbItem->getInsuranceValue(),
                    $this->awbItem->getRepayment(),
                    CarrierConstants::COD_COLLECTOR_CLIENT,
                    null,
                    $serviceTax->getServiceTaxIds(),
                    null,
                    $this->awbItem->getClientReference(),
                    $this->awbItem->getObservation(),
                    '',
                    '',
                    null,
                    $awbRecipient->getOoh()->getLockerId(),
                    null,
                    $awbRecipient->getOoh()->getOohLastMile(),
                    $awbRecipient->getCurrency()
                )
            );
        } catch (CourierServiceException $exception) {
            return new GenerateAwbResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR
            );
        }

        $postAwbGenerationResponse = $this->postAwbGenerationServiceProvider->apply(
            new PostAwbGenerationRequestDto(
                $this->awbItem->getOrderId(),
                $this->awbItem->getShippingLines(),
                $service,
                $awb->getAwbNumber(),
                (float) $awb->getCost(),
                $awb->getParcels()
            ),
            $carrierServiceRules,
            $this->courierServiceProvider
        );

        return new GenerateAwbResponse(
            $postAwbGenerationResponse->getMessage(),
            $postAwbGenerationResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }

    /**
     * @param array $packageDimensions
     *
     * @return array<int,
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
