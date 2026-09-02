<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

use SamedayCourier\Shipping\Domain\DTOs\Requests\DeletePickupPointRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

/**
 * @extends AbstractUseCase<DeletePickupPointRequest, DeletePickupPointResponse>
 *
 * @method DeletePickupPointResponse execute(DeletePickupPointRequest $request)
 */
final class DeletePickupPoint extends AbstractUseCase
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
     * @param DeletePickupPointRequest $request
     * @return DeletePickupPointResponse
     */
    protected function processAction(RequestInterface $request): DeletePickupPointResponse
    {
        try {
            $this->courierServiceProvider->deletePickupPoint(
                new DeletePickupPointRequestDto($request->getSamedayId())
            );
        } catch (CourierServiceException $exception) {
            return new DeletePickupPointResponse(
                'Failed to delete pickup point: ' . $exception->getMessage(),
                true
            );
        }

        return new DeletePickupPointResponse(
            'Pickup point successfully deleted.',
            false
        );
    }
}
