<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\CityObject;
use Sameday\Objects\CountyObject;
use Sameday\Objects\Locker\LockerObject;
use Sameday\Objects\PickupPoint\PickupPointContactPersonObject;
use Sameday\Objects\PickupPoint\PickupPointObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use Sameday\Objects\PostAwb\Request\ThirdPartyPickupEntityObject;
use Sameday\Objects\Service\ServiceObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\AwbPdfType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\DeliveryIntervalServiceType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayDeleteAwbRequest;
use Sameday\Requests\SamedayDeletePickupPointRequest;
use Sameday\Requests\SamedayGetAwbPdfRequest;
use Sameday\Requests\SamedayGetCitiesRequest;
use Sameday\Requests\SamedayGetCountiesRequest;
use Sameday\Requests\SamedayGetLockersRequest;
use Sameday\Requests\SamedayGetPickupPointsRequest;
use Sameday\Requests\SamedayGetServicesRequest;
use Sameday\Requests\SamedayPostAwbEstimationRequest;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Requests\SamedayPostParcelRequest;
use Sameday\Requests\SamedayPostPickupPointRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Domain\DTOs\CourierLockerDto;
use SamedayCourier\Shipping\Domain\DTOs\CourierPickupPointDto;
use SamedayCourier\Shipping\Domain\DTOs\CourierServiceDto;
use SamedayCourier\Shipping\Domain\DTOs\RecipientDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\DeletePickupPointRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\CourierLoginRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\EstimateCostRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\DeletePickupPointResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\CourierLoginResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\EstimateCostResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCitiesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetCitiesResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCountiesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetCountiesResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetLockersRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetLockersResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetParcelStatusHistoryRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetParcelStatusHistoryResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetPickupPointsRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetPickupPointsResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetServicesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetServicesResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\PostAwbResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostParcelRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\PostParcelResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostPickupPointRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\PostPickupPointResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RemoveAwbResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowAsPdfRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\ShowAsPdfResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayAwbParcelMapper;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\ParcelStatusHistoryService;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;

class CourierServiceProvider implements CourierServiceProviderInterface
{
    private ?Sameday $sameday;

    private AwbErrorParser $awbErrorParser;

    private ParcelStatusHistoryService $parcelStatusHistoryService;

    private ParcelDimensionsFactory $parcelDimensionsFactory;

    private SdkInitiator $sdkInitiator;

    /**
     * @param ?Sameday $sameday
     * @param ?AwbErrorParser $awbErrorParser
     * @param ?ParcelStatusHistoryService $parcelStatusHistoryService
     * @param ?ParcelDimensionsFactory $parcelDimensionsFactory
     * @param ?SdkInitiator $sdkInitiator
     */
    public function __construct(
        ?Sameday $sameday = null,
        ?AwbErrorParser $awbErrorParser = null,
        ?ParcelStatusHistoryService $parcelStatusHistoryService = null,
        ?ParcelDimensionsFactory $parcelDimensionsFactory = null,
        ?SdkInitiator $sdkInitiator = null
    ) {
        $this->sdkInitiator = $sdkInitiator ?? new SdkInitiator();
        $this->sameday = $sameday;
        $this->awbErrorParser = $awbErrorParser ?? new AwbErrorParser();
        $this->parcelStatusHistoryService = $parcelStatusHistoryService ?? new ParcelStatusHistoryService();
        $this->parcelDimensionsFactory = $parcelDimensionsFactory ?? new ParcelDimensionsFactory();
    }

    /**
     * @return Sameday
     */
    private function getSameday(): Sameday
    {
        if (null !== $this->sameday) {
            return $this->sameday;
        }

        try {
            $client = $this->sdkInitiator->init();
        } catch (SamedaySDKException $exception) {
            throw new CourierServiceException($exception->getMessage());
        }

        if (null === $client) {
            throw new CourierServiceException('Please provide valid credentials.');
        }

        return $this->sameday = new Sameday($client);
    }

    /**
     * @param PostAwbRequestDto $awbRequestDto
     *
     * @return PostAwbResponseDto
     * @throws CourierServiceException
     */
    public function postAwb(PostAwbRequestDto $awbRequestDto): PostAwbResponseDto
    {
        try {
            $cashOnDeliveryCollector = null !== $awbRequestDto->getCashOnDeliveryCollector()
                ? new CodCollectorType($awbRequestDto->getCashOnDeliveryCollector())
                : null;
            $deliveryIntervalServiceType = null !== $awbRequestDto->getDeliveryIntervalServiceType()
                ? new DeliveryIntervalServiceType($awbRequestDto->getDeliveryIntervalServiceType())
                : null;
            $thirdPartyPickup = $awbRequestDto->getThirdPartyPickup();
            if (!$thirdPartyPickup instanceof ThirdPartyPickupEntityObject) {
                $thirdPartyPickup = null;
            }

            $postAwb = $this->getSameday()->postAwb(
                new SamedayPostAwbRequest(
                    $awbRequestDto->getPickupPointId(),
                    $awbRequestDto->getContactPersonId(),
                    new PackageType($awbRequestDto->getPackageType()),
                    $this->parcelDimensionsFactory->fromList($awbRequestDto->getParcelsDimensions()),
                    $awbRequestDto->getServiceId(),
                    new AwbPaymentType($awbRequestDto->getAwbPayment()),
                    $this->toAwbRecipientEntity($awbRequestDto->getAwbRecipient()),
                    $awbRequestDto->getInsuredValue(),
                    $awbRequestDto->getCashOnDeliveryAmount(),
                    $cashOnDeliveryCollector,
                    $thirdPartyPickup,
                    $awbRequestDto->getServiceTaxIds(),
                    $deliveryIntervalServiceType,
                    $awbRequestDto->getReference(),
                    $awbRequestDto->getObservation(),
                    $awbRequestDto->getPriceObservation(),
                    $awbRequestDto->getClientObservation(),
                    $awbRequestDto->getLockerFirstMile(),
                    $awbRequestDto->getLockerLastMile(),
                    $awbRequestDto->getOohFirstMile(),
                    $awbRequestDto->getOohLastMile(),
                    $awbRequestDto->getCurrency()
                )
            );

            return new PostAwbResponseDto(
                $postAwb->getAwbNumber(),
                $postAwb->getCost(),
                (new SamedayAwbParcelMapper())->mapCollection($postAwb->getParcels())
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param RecipientDto $recipient
     *
     * @return AwbRecipientEntityObject
     */
    private function toAwbRecipientEntity(RecipientDto $recipient): AwbRecipientEntityObject
    {
        $companyName = $recipient->getCompany();
        $company = (null !== $companyName && '' !== $companyName)
            ? new CompanyEntityObject($companyName)
            : null;

        return new AwbRecipientEntityObject(
            $recipient->getCity(),
            $recipient->getCounty(),
            $recipient->getAddress(),
            $recipient->getName(),
            $recipient->getPhone(),
            $recipient->getEmail(),
            $company,
            $recipient->getPostcode(),
        );
    }

    /**
     * @param RemoveAwbRequestDto $removeAwbRequestDto
     *
     * @return RemoveAwbResponseDto
     * @throws CourierServiceException
     */
    public function removeAwb(RemoveAwbRequestDto $removeAwbRequestDto): RemoveAwbResponseDto
    {
        try {
            $this->getSameday()->deleteAwb(
                new SamedayDeleteAwbRequest($removeAwbRequestDto->getAwb())
            );

            return new RemoveAwbResponseDto();
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param ShowAsPdfRequestDto $showAsPdfRequestDto
     *
     * @return ShowAsPdfResponseDto
     * @throws CourierServiceException
     */
    public function showAsPdf(ShowAsPdfRequestDto $showAsPdfRequestDto): ShowAsPdfResponseDto
    {
        try {
            $pdfResponse = $this->getSameday()->getAwbPdf(
                new SamedayGetAwbPdfRequest(
                    $showAsPdfRequestDto->getAwbNumber(),
                    new AwbPdfType($showAsPdfRequestDto->getAwbPdfType())
                )
            );

            return new ShowAsPdfResponseDto($pdfResponse->getPdf());
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param PostParcelRequestDto $postParcelRequestDto
     *
     * @return PostParcelResponseDto
     * @throws CourierServiceException
     */
    public function postParcel(PostParcelRequestDto $postParcelRequestDto): PostParcelResponseDto
    {
        try {
            $parcel = $this->getSameday()->postParcel(
                new SamedayPostParcelRequest(
                    $postParcelRequestDto->getAwbNumber(),
                    $this->parcelDimensionsFactory->fromAttributes(
                        $postParcelRequestDto->getParcelWeight(),
                        $postParcelRequestDto->getParcelWidth(),
                        $postParcelRequestDto->getParcelLength(),
                        $postParcelRequestDto->getParcelHeight()
                    ),
                    $postParcelRequestDto->getPosition(),
                    $postParcelRequestDto->getObservation(),
                    $postParcelRequestDto->getPriceObservation(),
                    $postParcelRequestDto->isLast()
                )
            );

            return new PostParcelResponseDto($parcel->getParcelAwbNumber());
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param GetParcelStatusHistoryRequestDto $requestDto
     *
     * @return GetParcelStatusHistoryResponseDto
     * @throws CourierServiceException
     */
    public function getParcelStatusHistory(
        GetParcelStatusHistoryRequestDto $requestDto
    ): GetParcelStatusHistoryResponseDto {
        try {
            $response = $this->parcelStatusHistoryService->get(
                $this->getSameday(),
                $requestDto->getParcel()
            );

            return new GetParcelStatusHistoryResponseDto(
                $response->getSummary(),
                $response->getHistory(),
                $response->getExpeditionStatus()
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param GetCitiesRequestDto $requestDto
     *
     * @return GetCitiesResponseDto
     * @throws CourierServiceException
     */
    public function getCities(GetCitiesRequestDto $requestDto): GetCitiesResponseDto
    {
        try {
            $request = new SamedayGetCitiesRequest(
                $requestDto->getCountyId(),
                $requestDto->getName(),
                $requestDto->getPostalCode()
            );
            $request->setPage($requestDto->getPage());
            $response = $this->getSameday()->getCities($request);

            return new GetCitiesResponseDto(
                array_map(
                    /**
                     * @param CityObject $city
                     *
                     * @return array
                     */
                    static function (CityObject $city): array {
                        return [
                            'id' => $city->getId(),
                            'name' => $city->getName(),
                        ];
                    },
                    $response->getCities()
                ),
                $response->getPages()
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param GetCountiesRequestDto $requestDto
     *
     * @return GetCountiesResponseDto
     * @throws CourierServiceException
     */
    public function getCounties(GetCountiesRequestDto $requestDto): GetCountiesResponseDto
    {
        try {
            $response = $this->getSameday()->getCounties(
                new SamedayGetCountiesRequest($requestDto->getName())
            );

            return new GetCountiesResponseDto(
                array_map(
                    /**
                     * @param CountyObject $county
                     *
                     * @return array
                     */
                    static function (CountyObject $county): array {
                        return [
                            'id' => $county->getId(),
                            'name' => $county->getName(),
                        ];
                    },
                    $response->getCounties()
                )
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param GetServicesRequestDto $requestDto
     *
     * @return GetServicesResponseDto
     * @throws CourierServiceException
     */
    public function getServices(GetServicesRequestDto $requestDto): GetServicesResponseDto
    {
        try {
            $request = new SamedayGetServicesRequest();
            $request->setPage($requestDto->getPage());
            $response = $this->getSameday()->getServices($request);

            return new GetServicesResponseDto(
                array_map(
                    /**
                     * @param ServiceObject $service
                     *
                     * @return CourierServiceDto
                     */
                    static function (ServiceObject $service): CourierServiceDto {
                        $optionalTaxes = $service->getOptionalTaxes();

                        return new CourierServiceDto(
                            $service->getId(),
                            $service->getName(),
                            $service->getCode(),
                            !empty($optionalTaxes) ? serialize($optionalTaxes) : null
                        );
                    },
                    $response->getServices()
                ),
                $response->getPages()
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param GetLockersRequestDto $requestDto
     *
     * @return GetLockersResponseDto
     * @throws CourierServiceException
     */
    public function getLockers(GetLockersRequestDto $requestDto): GetLockersResponseDto
    {
        try {
            $request = new SamedayGetLockersRequest($requestDto->getLockerIds());
            $request->setPage($requestDto->getPage());
            $response = $this->getSameday()->getLockers($request);

            return new GetLockersResponseDto(
                array_map(
                    /**
                     * @param LockerObject $locker
                     *
                     * @return CourierLockerDto
                     */
                    static function (LockerObject $locker): CourierLockerDto {
                        return new CourierLockerDto(
                            $locker->getId(),
                            $locker->getName(),
                            $locker->getCity(),
                            $locker->getCounty(),
                            $locker->getAddress(),
                            (string) $locker->getLat(),
                            (string) $locker->getLong(),
                            (string) $locker->getPostalCode(),
                            serialize($locker->getBoxes())
                        );
                    },
                    $response->getLockers()
                ),
                $response->getPages()
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param GetPickupPointsRequestDto $requestDto
     *
     * @return GetPickupPointsResponseDto
     * @throws CourierServiceException
     */
    public function getPickupPoints(GetPickupPointsRequestDto $requestDto): GetPickupPointsResponseDto
    {
        try {
            $request = new SamedayGetPickupPointsRequest();
            $request->setPage($requestDto->getPage());
            $response = $this->getSameday()->getPickupPoints($request);

            return new GetPickupPointsResponseDto(
                array_map(
                    /**
                     * @param PickupPointObject $pickupPoint
                     *
                     * @return CourierPickupPointDto
                     */
                    static function (PickupPointObject $pickupPoint): CourierPickupPointDto {
                        return new CourierPickupPointDto(
                            $pickupPoint->getId(),
                            $pickupPoint->getAlias(),
                            $pickupPoint->getCity()->getName(),
                            $pickupPoint->getCounty()->getName(),
                            $pickupPoint->getAddress(),
                            $pickupPoint->isDefault(),
                            serialize($pickupPoint->getContactPersons())
                        );
                    },
                    $response->getPickupPoints()
                ),
                $response->getPages()
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param PostPickupPointRequestDto $requestDto
     *
     * @return PostPickupPointResponseDto
     * @throws CourierServiceException
     */
    public function postPickupPoint(PostPickupPointRequestDto $requestDto): PostPickupPointResponseDto
    {
        try {
            $contactPersons = array_map(
                static function (array $contactPerson): PickupPointContactPersonObject {
                    return new PickupPointContactPersonObject(
                        $contactPerson['name'],
                        $contactPerson['phone'],
                        $contactPerson['default']
                    );
                },
                $requestDto->getContactPersons()
            );

            $this->getSameday()->postPickupPoint(
                new SamedayPostPickupPointRequest(
                    $requestDto->getCountryId(),
                    $requestDto->getCountyId(),
                    $requestDto->getCityId(),
                    $requestDto->getAddress(),
                    $requestDto->getPostalCode(),
                    $requestDto->getAlias(),
                    $contactPersons,
                    $requestDto->isDefaultPickupPoint()
                )
            );

            return new PostPickupPointResponseDto();
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param DeletePickupPointRequestDto $requestDto
     *
     * @return DeletePickupPointResponseDto
     * @throws CourierServiceException
     */
    public function deletePickupPoint(DeletePickupPointRequestDto $requestDto): DeletePickupPointResponseDto
    {
        try {
            $this->getSameday()->deletePickupPoint(
                new SamedayDeletePickupPointRequest($requestDto->getPickupPointId())
            );

            return new DeletePickupPointResponseDto();
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param EstimateCostRequestDto $requestDto
     *
     * @return EstimateCostResponseDto
     * @throws CourierServiceException
     */
    public function estimateCost(EstimateCostRequestDto $requestDto): EstimateCostResponseDto
    {
        try {
            $thirdPartyPickup = $requestDto->getThirdPartyPickup();
            if (!$thirdPartyPickup instanceof ThirdPartyPickupEntityObject) {
                $thirdPartyPickup = null;
            }

            $estimation = $this->getSameday()->postAwbEstimation(
                new SamedayPostAwbEstimationRequest(
                    $requestDto->getPickupPointId(),
                    $requestDto->getContactPersonId(),
                    new PackageType($requestDto->getPackageType()),
                    $this->parcelDimensionsFactory->fromList($requestDto->getParcelsDimensions()),
                    $requestDto->getServiceId(),
                    new AwbPaymentType($requestDto->getAwbPayment()),
                    $this->toAwbRecipientEntity($requestDto->getAwbRecipient()),
                    $requestDto->getInsuredValue(),
                    $requestDto->getCashOnDeliveryAmount(),
                    $thirdPartyPickup,
                    $requestDto->getServiceTaxIds(),
                    $requestDto->getCurrency()
                )
            );

            return new EstimateCostResponseDto(
                $estimation->getCost(),
                $estimation->getCurrency()
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @param CourierLoginRequestDto $requestDto
     *
     * @return CourierLoginResponseDto
     */
    public function login(CourierLoginRequestDto $requestDto): CourierLoginResponseDto
    {
        try {
            $client = $this->sdkInitiator->init(
                $requestDto->getUser(),
                $requestDto->getPass(),
                $requestDto->getApiUrl()
            );

            if (null === $client) {
                return new CourierLoginResponseDto(false);
            }

            return new CourierLoginResponseDto($client->login());
        } catch (Exception $exception) {
            return new CourierLoginResponseDto(false);
        }
    }

    /**
     * @param Exception $exception
     *
     * @return CourierServiceException
     */
    private function toCourierServiceException(Exception $exception): CourierServiceException
    {
        if ($exception instanceof CourierServiceException) {
            return $exception;
        }

        return new CourierServiceException(
            $this->awbErrorParser->parse($this->extractErrors($exception)),
            (int) $exception->getCode(),
            $exception
        );
    }

    /**
     * @param Exception $exception
     *
     * @return array<int, mixed>
     */
    private function extractErrors(Exception $exception): array
    {
        if ($exception instanceof SamedayBadRequestException) {
            $errors = $exception->getErrors();
            try {
                $rawResponse = $exception->getRawResponse()->getBody();
                $decoded = json_decode($rawResponse, true, 512, JSON_THROW_ON_ERROR);
                $errorMessages = is_array($decoded)
                    ? ($decoded['errors']['errors'] ?? null)
                    : null;

                if (null !== $errorMessages) {
                    $errors[] = [
                        'key' => ['Validation Failed', ''],
                        'errors' => $errorMessages,
                    ];
                }
            } catch (JsonException $jsonException) {
                $errors[] = [
                    'key' => 'JSON Validation Failed',
                    'errors' => $jsonException->getMessage(),
                ];
            }

            return $errors;
        }

        if ($exception instanceof SamedayOtherException) {
            $errors = [];
            $error = $exception->getRawResponse()->getBody();
            if (null !== $error && '' !== $error) {
                try {
                    $error = json_decode($error, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $jsonException) {
                    return [
                        [
                            'message' => $jsonException->getMessage(),
                        ],
                    ];
                }
            }

            if (is_array($error) && null !== ($parsedError = $error['error'] ?? null)) {
                $errors[] = $parsedError;
            }

            return $errors;
        }

        $message = $exception->getMessage();
        if ('' === $message) {
            $message = 'The request could not be processed!';
        }

        return [
            [
                'code' => $exception->getCode(),
                'message' => $message,
            ],
        ];
    }
}
