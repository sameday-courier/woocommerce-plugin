<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\DeletePickupPointServiceRequestDto;
use SamedayCourier\Shipping\Domain\Ports\DeletePickupPointServiceProviderInterface;

final class DeletePickupPoint
{
    private DeletePickupPointItem $deletePickupPointItem;

    private DeletePickupPointServiceProviderInterface $deletePickupPointServiceProvider;

    public function __construct(DeletePickupPointRequest $deletePickupPointRequest)
    {
        $this->deletePickupPointItem = $deletePickupPointRequest->getDeletePickupPointItem();
        $this->deletePickupPointServiceProvider = $deletePickupPointRequest->getDeletePickupPointServiceProvider();
    }

    public function execute(): DeletePickupPointResponse
    {
        $response = $this->deletePickupPointServiceProvider->delete(
            new DeletePickupPointServiceRequestDto($this->deletePickupPointItem->getSamedayId())
        );

        return new DeletePickupPointResponse(
            $response->getMessage(),
            $response->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
