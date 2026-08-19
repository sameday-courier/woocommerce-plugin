<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\DeletePickupPointRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class DeletePickupPoint
{
    private DeletePickupPointItem $deletePickupPointItem;

    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @param DeletePickupPointRequest $deletePickupPointRequest
     */
    public function __construct(DeletePickupPointRequest $deletePickupPointRequest)
    {
        $this->deletePickupPointItem = $deletePickupPointRequest->getDeletePickupPointItem();
        $this->courierServiceProvider = $deletePickupPointRequest->getCourierServiceProvider();
    }

    /**
     * @return DeletePickupPointResponse
     */
    public function execute(): DeletePickupPointResponse
    {
        try {
            $this->courierServiceProvider->deletePickupPoint(
                new DeletePickupPointRequestDto($this->deletePickupPointItem->getSamedayId())
            );
        } catch (CourierServiceException $exception) {
            return new DeletePickupPointResponse(
                'Failed to delete pickup point: ' . $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new DeletePickupPointResponse(
            'Pickup point successfully deleted.',
            ResponseNoticeType::SUCCESS,
        );
    }
}
