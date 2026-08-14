<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use Sameday\Objects\CountyObject;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\GetCountiesRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class GetCounties
{
    private CourierServiceProviderInterface $courier;

    public function __construct(GetCountiesRequest $getCountiesRequest)
    {
        $this->courier = $getCountiesRequest->getCourier();
    }

    /**
     * @return GetCountiesResponse
     */
    public function execute(): GetCountiesResponse
    {
        try {
            $samedayCounties = $this->courier
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
            array_map(
                static function (CountyObject $county): array {
                    return [
                        'id' => $county->getId(),
                        'name' => $county->getName(),
                    ];
                },
                $samedayCounties
            ),
        );
    }
}
