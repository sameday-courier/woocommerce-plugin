<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCountiesRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class GetCounties
{
    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @param GetCountiesRequest $getCountiesRequest
     */
    public function __construct(GetCountiesRequest $getCountiesRequest)
    {
        $this->courierServiceProvider = $getCountiesRequest->getCourierServiceProvider();
    }

    /**
     * @return GetCountiesResponse
     */
    public function execute(): GetCountiesResponse
    {
        try {
            $counties = $this->courierServiceProvider
                ->getCounties(new GetCountiesRequestDto(null))
                ->getCounties();
        } catch (CourierServiceException $exception) {
            return new GetCountiesResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new GetCountiesResponse(
            null,
            ResponseNoticeType::SUCCESS,
            $counties,
        );
    }
}
