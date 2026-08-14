<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

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

interface CourierServiceProviderInterface
{
    /**
     * @throws CourierServiceException
     */
    public function postAwb(PostAwbRequestDto $awbRequestDto): PostAwbResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function removeAwb(RemoveAwbRequestDto $removeAwbRequestDto): RemoveAwbResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function showAsPdf(ShowAsPdfRequestDto $showAsPdfRequestDto): ShowAsPdfResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function postParcel(PostParcelRequestDto $postParcelRequestDto): PostParcelResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function getParcelStatusHistory(
        GetParcelStatusHistoryRequestDto $requestDto
    ): GetParcelStatusHistoryResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function getCities(GetCitiesRequestDto $requestDto): GetCitiesResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function getCounties(GetCountiesRequestDto $requestDto): GetCountiesResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function getServices(GetServicesRequestDto $requestDto): GetServicesResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function getLockers(GetLockersRequestDto $requestDto): GetLockersResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function getPickupPoints(GetPickupPointsRequestDto $requestDto): GetPickupPointsResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function postPickupPoint(PostPickupPointRequestDto $requestDto): PostPickupPointResponseDto;

    /**
     * @throws CourierServiceException
     */
    public function deletePickupPoint(DeletePickupPointRequestDto $requestDto): DeletePickupPointResponseDto;
}
