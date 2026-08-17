<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\CountyObject;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCountiesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCountiesServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetCountiesServiceResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\GetCountiesServiceProviderInterface;

final class GetCountiesServiceProvider implements GetCountiesServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    public function __construct(?CourierServiceProviderInterface $courier = null)
    {
        $this->courier = $courier ?? new CourierServiceProvider();
    }

    /**
     * @param GetCountiesServiceRequestDto $getCountiesServiceRequestDto
     *
     * @return GetCountiesServiceResponseDto
     */
    public function get(GetCountiesServiceRequestDto $getCountiesServiceRequestDto): GetCountiesServiceResponseDto
    {
        try {
            $samedayCounties = $this->courier
                ->getCounties(new GetCountiesRequestDto(null))
                ->getCounties();
        } catch (CourierServiceException $exception) {
            return new GetCountiesServiceResponseDto(
                false,
                $exception->getMessage()
            );
        }

        return new GetCountiesServiceResponseDto(
            true,
            'Counties retrieved successfully.',
            array_map(
                static function (CountyObject $county): array {
                    return [
                        'id' => $county->getId(),
                        'name' => $county->getName(),
                    ];
                },
                $samedayCounties
            )
        );
    }
}
