<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\Requests\DeletePickupPointRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\DeletePickupPointServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\DeletePickupPointServiceResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\DeletePickupPointServiceProviderInterface;

final class DeletePickupPointServiceProvider implements DeletePickupPointServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    public function __construct(?CourierServiceProviderInterface $courier = null)
    {
        $this->courier = $courier ?? new CourierServiceProvider();
    }

    /**
     * @param DeletePickupPointServiceRequestDto $deletePickupPointServiceRequestDto
     *
     * @return DeletePickupPointServiceResponseDto
     */
    public function delete(
        DeletePickupPointServiceRequestDto $deletePickupPointServiceRequestDto
    ): DeletePickupPointServiceResponseDto {
        try {
            $this->courier->deletePickupPoint(
                new DeletePickupPointRequestDto($deletePickupPointServiceRequestDto->getSamedayId())
            );
        } catch (CourierServiceException $exception) {
            return new DeletePickupPointServiceResponseDto(
                false,
                'Failed to delete pickup point: ' . $exception->getMessage()
            );
        }

        return new DeletePickupPointServiceResponseDto(
            true,
            'Pickup point successfully deleted.'
        );
    }
}
