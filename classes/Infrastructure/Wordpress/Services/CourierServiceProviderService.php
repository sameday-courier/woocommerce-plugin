<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Exceptions\SamedayOtherException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayDeleteAwbRequest;
use Sameday\Requests\SamedayDeletePickupPointRequest;
use Sameday\Requests\SamedayGetAwbPdfRequest;
use Sameday\Requests\SamedayGetCitiesRequest;
use Sameday\Requests\SamedayGetCountiesRequest;
use Sameday\Requests\SamedayGetLockersRequest;
use Sameday\Requests\SamedayGetPickupPointsRequest;
use Sameday\Requests\SamedayGetServicesRequest;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Requests\SamedayPostParcelRequest;
use Sameday\Requests\SamedayPostPickupPointRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Domain\DTOs\DeletePickupPointRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\DeletePickupPointResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\GetCitiesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\GetCitiesResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\GetCountiesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\GetCountiesResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\GetLockersRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\GetLockersResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\GetParcelStatusHistoryRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\GetParcelStatusHistoryResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\GetPickupPointsRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\GetPickupPointsResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\GetServicesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\GetServicesResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\PostAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\PostAwbResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\PostParcelRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\PostParcelResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\PostPickupPointRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\PostPickupPointResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\RemoveAwbResponseDto;
use SamedayCourier\Shipping\Domain\DTOs\ShowAsPdfRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\ShowAsPdfResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\ParcelStatusHistoryService;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;

class CourierServiceProviderService implements CourierServiceProviderInterface
{
    private Sameday $sameday;

    private AwbErrorParser $awbErrorParser;

    private ParcelStatusHistoryService $parcelStatusHistoryService;

    /**
     * @throws SamedaySDKException
     */
    public function __construct(
        ?Sameday $sameday = null,
        ?AwbErrorParser $awbErrorParser = null,
        ?ParcelStatusHistoryService $parcelStatusHistoryService = null
    ) {
        $this->sameday = $sameday ?? new Sameday(SdkInitiator::init());
        $this->awbErrorParser = $awbErrorParser ?? new AwbErrorParser();
        $this->parcelStatusHistoryService = $parcelStatusHistoryService ?? new ParcelStatusHistoryService();
    }

    /**
     * @throws CourierServiceException
     */
    public function postAwb(PostAwbRequestDto $awbRequestDto): PostAwbResponseDto
    {
        try {
            $postAwb = $this->sameday->postAwb(
                new SamedayPostAwbRequest(
                    $awbRequestDto->getPickupPointId(),
                    $awbRequestDto->getContactPersonId(),
                    $awbRequestDto->getPackageType(),
                    $awbRequestDto->getParcelsDimensions(),
                    $awbRequestDto->getServiceId(),
                    $awbRequestDto->getAwbPayment(),
                    $awbRequestDto->getAwbRecipient(),
                    $awbRequestDto->getInsuredValue(),
                    $awbRequestDto->getCashOnDeliveryAmount(),
                    $awbRequestDto->getCashOnDeliveryCollector(),
                    $awbRequestDto->getThirdPartyPickup(),
                    $awbRequestDto->getServiceTaxIds(),
                    $awbRequestDto->getDeliveryIntervalServiceType(),
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
                $postAwb->getParcels()
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @throws CourierServiceException
     */
    public function removeAwb(RemoveAwbRequestDto $removeAwbRequestDto): RemoveAwbResponseDto
    {
        try {
            $this->sameday->deleteAwb(
                new SamedayDeleteAwbRequest($removeAwbRequestDto->getAwb())
            );

            return new RemoveAwbResponseDto();
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @throws CourierServiceException
     */
    public function showAsPdf(ShowAsPdfRequestDto $showAsPdfRequestDto): ShowAsPdfResponseDto
    {
        try {
            $pdfResponse = $this->sameday->getAwbPdf(
                new SamedayGetAwbPdfRequest(
                    $showAsPdfRequestDto->getAwbNumber(),
                    $showAsPdfRequestDto->getAwbPdfType()
                )
            );

            return new ShowAsPdfResponseDto($pdfResponse->getPdf());
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @throws CourierServiceException
     */
    public function postParcel(PostParcelRequestDto $postParcelRequestDto): PostParcelResponseDto
    {
        try {
            $parcel = $this->sameday->postParcel(
                new SamedayPostParcelRequest(
                    $postParcelRequestDto->getAwbNumber(),
                    $postParcelRequestDto->getParcelDimensions(),
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
     * @throws CourierServiceException
     */
    public function getParcelStatusHistory(
        GetParcelStatusHistoryRequestDto $requestDto
    ): GetParcelStatusHistoryResponseDto {
        try {
            $response = $this->parcelStatusHistoryService->get(
                $this->sameday,
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
            $response = $this->sameday->getCities($request);

            return new GetCitiesResponseDto($response->getCities(), $response->getPages());
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @throws CourierServiceException
     */
    public function getCounties(GetCountiesRequestDto $requestDto): GetCountiesResponseDto
    {
        try {
            $response = $this->sameday->getCounties(
                new SamedayGetCountiesRequest($requestDto->getName())
            );

            return new GetCountiesResponseDto($response->getCounties());
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @throws CourierServiceException
     */
    public function getServices(GetServicesRequestDto $requestDto): GetServicesResponseDto
    {
        try {
            $request = new SamedayGetServicesRequest();
            $request->setPage($requestDto->getPage());
            $response = $this->sameday->getServices($request);

            return new GetServicesResponseDto($response->getServices(), $response->getPages());
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @throws CourierServiceException
     */
    public function getLockers(GetLockersRequestDto $requestDto): GetLockersResponseDto
    {
        try {
            $request = new SamedayGetLockersRequest($requestDto->getLockerIds());
            $request->setPage($requestDto->getPage());
            $response = $this->sameday->getLockers($request);

            return new GetLockersResponseDto($response->getLockers(), $response->getPages());
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @throws CourierServiceException
     */
    public function getPickupPoints(GetPickupPointsRequestDto $requestDto): GetPickupPointsResponseDto
    {
        try {
            $request = new SamedayGetPickupPointsRequest();
            $request->setPage($requestDto->getPage());
            $response = $this->sameday->getPickupPoints($request);

            return new GetPickupPointsResponseDto(
                $response->getPickupPoints(),
                $response->getPages()
            );
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @throws CourierServiceException
     */
    public function postPickupPoint(PostPickupPointRequestDto $requestDto): PostPickupPointResponseDto
    {
        try {
            $this->sameday->postPickupPoint(
                new SamedayPostPickupPointRequest(
                    $requestDto->getCountryId(),
                    $requestDto->getCountyId(),
                    $requestDto->getCityId(),
                    $requestDto->getAddress(),
                    $requestDto->getPostalCode(),
                    $requestDto->getAlias(),
                    $requestDto->getContactPersons(),
                    $requestDto->isDefaultPickupPoint()
                )
            );

            return new PostPickupPointResponseDto();
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

    /**
     * @throws CourierServiceException
     */
    public function deletePickupPoint(DeletePickupPointRequestDto $requestDto): DeletePickupPointResponseDto
    {
        try {
            $this->sameday->deletePickupPoint(
                new SamedayDeletePickupPointRequest($requestDto->getPickupPointId())
            );

            return new DeletePickupPointResponseDto();
        } catch (Exception $exception) {
            throw $this->toCourierServiceException($exception);
        }
    }

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
     * @return array<int, mixed>
     */
    private function extractErrors(Exception $exception): array
    {
        if ($exception instanceof SamedayBadRequestException) {
            $errors = $exception->getErrors();
            try {
                $rawResponse = $exception->getRawResponse()->getBody();
                $errorMessages = json_decode($rawResponse, false, 512, JSON_THROW_ON_ERROR)
                    ->errors
                    ->errors
                ;
                $errors[] = [
                    'key' => ['Validation Failed', ''],
                    'errors' => $errorMessages,
                ];
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
