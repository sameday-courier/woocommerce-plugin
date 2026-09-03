<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCountiesRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

/**
 * @extends AbstractUseCase<GetCountiesRequest, GetCountiesResponse>
 *
 * @method GetCountiesResponse execute(GetCountiesRequest $request)
 */
final class GetCounties extends AbstractUseCase
{
    /**
     * @var CourierServiceProviderInterface $courierServiceProvider
     */
    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @param CourierServiceProviderInterface $courierServiceProvider
     */
    public function __construct(
        CourierServiceProviderInterface $courierServiceProvider
    ) {
        $this->courierServiceProvider = $courierServiceProvider;
    }

    /**
     * @param GetCountiesRequest $request
     * @return GetCountiesResponse
     */
    protected function processAction(RequestInterface $request): GetCountiesResponse
    {
        try {
            $counties = $this->courierServiceProvider
                ->getCounties(new GetCountiesRequestDto(null))
                ->getCounties();
        } catch (CourierServiceException $exception) {
            return new GetCountiesResponse(
                $exception->getMessage(),
                true
            );
        }

        return new GetCountiesResponse(
            '',
            false,
            $counties
        );
    }
}
