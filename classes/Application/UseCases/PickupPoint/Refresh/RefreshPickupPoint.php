<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RefreshPickupPointRequestDto;
use SamedayCourier\Shipping\Domain\Ports\RefreshPickupPointServiceProviderInterface;

final class RefreshPickupPoint
{
    private RefreshPickupPointServiceProviderInterface $refreshPickupPointServiceProvider;

    /**
     * @param RefreshPickupPointRequest $refreshPickupPointRequest
     */
    public function __construct(RefreshPickupPointRequest $refreshPickupPointRequest)
    {
        $this->refreshPickupPointServiceProvider = $refreshPickupPointRequest->getRefreshPickupPointServiceProvider();
    }

    /**
     * @return RefreshPickupPointResponse
     */
    public function execute(): RefreshPickupPointResponse
    {
        $refreshPickupPointResponse = $this->refreshPickupPointServiceProvider->refresh(
            new RefreshPickupPointRequestDto()
        );

        return new RefreshPickupPointResponse(
            $refreshPickupPointResponse->getMessage(),
            $refreshPickupPointResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
