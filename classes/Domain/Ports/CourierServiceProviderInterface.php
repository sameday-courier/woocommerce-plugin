<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

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

interface CourierServiceProviderInterface
{
    /**
     * @param CourierLoginRequestDto $requestDto
     *
     * @return CourierLoginResponseDto
     */
    public function login(CourierLoginRequestDto $requestDto): CourierLoginResponseDto;

    /**
     * @param PostAwbRequestDto $awbRequestDto
     *
     * @return PostAwbResponseDto
     * @throws CourierServiceException
     */
    public function postAwb(PostAwbRequestDto $awbRequestDto): PostAwbResponseDto;

    /**
     * @param RemoveAwbRequestDto $removeAwbRequestDto
     *
     * @return RemoveAwbResponseDto
     * @throws CourierServiceException
     */
    public function removeAwb(RemoveAwbRequestDto $removeAwbRequestDto): RemoveAwbResponseDto;

    /**
     * @param ShowAsPdfRequestDto $showAsPdfRequestDto
     *
     * @return ShowAsPdfResponseDto
     * @throws CourierServiceException
     */
    public function showAsPdf(ShowAsPdfRequestDto $showAsPdfRequestDto): ShowAsPdfResponseDto;

    /**
     * @param PostParcelRequestDto $postParcelRequestDto
     *
     * @return PostParcelResponseDto
     * @throws CourierServiceException
     */
    public function postParcel(PostParcelRequestDto $postParcelRequestDto): PostParcelResponseDto;

    /**
     * @param GetParcelStatusHistoryRequestDto $requestDto
     *
     * @return GetParcelStatusHistoryResponseDto
     * @throws CourierServiceException
     */
    public function getParcelStatusHistory(
        GetParcelStatusHistoryRequestDto $requestDto
    ): GetParcelStatusHistoryResponseDto;

    /**
     * @param GetCitiesRequestDto $requestDto
     *
     * @return GetCitiesResponseDto
     * @throws CourierServiceException
     */
    public function getCities(GetCitiesRequestDto $requestDto): GetCitiesResponseDto;

    /**
     * @param GetCountiesRequestDto $requestDto
     *
     * @return GetCountiesResponseDto
     * @throws CourierServiceException
     */
    public function getCounties(GetCountiesRequestDto $requestDto): GetCountiesResponseDto;

    /**
     * @param GetServicesRequestDto $requestDto
     *
     * @return GetServicesResponseDto
     * @throws CourierServiceException
     */
    public function getServices(GetServicesRequestDto $requestDto): GetServicesResponseDto;

    /**
     * @param GetLockersRequestDto $requestDto
     *
     * @return GetLockersResponseDto
     * @throws CourierServiceException
     */
    public function getLockers(GetLockersRequestDto $requestDto): GetLockersResponseDto;

    /**
     * @param GetPickupPointsRequestDto $requestDto
     *
     * @return GetPickupPointsResponseDto
     * @throws CourierServiceException
     */
    public function getPickupPoints(GetPickupPointsRequestDto $requestDto): GetPickupPointsResponseDto;

    /**
     * @param PostPickupPointRequestDto $requestDto
     *
     * @return PostPickupPointResponseDto
     * @throws CourierServiceException
     */
    public function postPickupPoint(PostPickupPointRequestDto $requestDto): PostPickupPointResponseDto;

    /**
     * @param DeletePickupPointRequestDto $requestDto
     *
     * @return DeletePickupPointResponseDto
     * @throws CourierServiceException
     */
    public function deletePickupPoint(DeletePickupPointRequestDto $requestDto): DeletePickupPointResponseDto;

    /**
     * @param EstimateCostRequestDto $requestDto
     *
     * @return EstimateCostResponseDto
     * @throws CourierServiceException
     */
    public function estimateCost(EstimateCostRequestDto $requestDto): EstimateCostResponseDto;
}
