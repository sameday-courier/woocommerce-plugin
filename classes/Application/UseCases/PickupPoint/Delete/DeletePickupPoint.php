<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\DeletePickupPointRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class DeletePickupPoint
{
    private CourierServiceProviderInterface $courier;

    private int $samedayId;

    public function __construct(DeletePickupPointRequest $deletePickupPointRequest)
    {
        $this->courier = $deletePickupPointRequest->getCourier();
        $this->samedayId = $deletePickupPointRequest->getDeletePickupPointItem()->getSamedayId();
    }

    public function execute(): DeletePickupPointResponse
    {
        try {
            $this->courier->deletePickupPoint(
                new DeletePickupPointRequestDto($this->samedayId)
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
