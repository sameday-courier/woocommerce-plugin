<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCountiesServiceRequestDto;
use SamedayCourier\Shipping\Domain\Ports\GetCountiesServiceProviderInterface;

final class GetCounties
{
    private GetCountiesServiceProviderInterface $getCountiesServiceProvider;

    public function __construct(GetCountiesRequest $getCountiesRequest)
    {
        $this->getCountiesServiceProvider = $getCountiesRequest->getGetCountiesServiceProvider();
    }

    /**
     * @return GetCountiesResponse
     */
    public function execute(): GetCountiesResponse
    {
        $response = $this->getCountiesServiceProvider->get(new GetCountiesServiceRequestDto());

        if (!$response->isSuccess()) {
            return new GetCountiesResponse(
                $response->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new GetCountiesResponse(
            null,
            ResponseNoticeType::SUCCESS,
            $response->getCounties(),
        );
    }
}
