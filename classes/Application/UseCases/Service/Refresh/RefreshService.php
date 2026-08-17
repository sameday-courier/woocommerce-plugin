<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RefreshServiceRequestDto;
use SamedayCourier\Shipping\Domain\Ports\RefreshServiceServiceProviderInterface;

final class RefreshService
{
    private RefreshServiceServiceProviderInterface $refreshServiceServiceProvider;

    /**
     * @param RefreshServiceRequest $refreshServiceRequest
     */
    public function __construct(RefreshServiceRequest $refreshServiceRequest)
    {
        $this->refreshServiceServiceProvider = $refreshServiceRequest->getRefreshServiceServiceProvider();
    }

    /**
     * @return RefreshServiceResponse
     */
    public function execute(): RefreshServiceResponse
    {
        $refreshServiceResponse = $this->refreshServiceServiceProvider->refresh(
            new RefreshServiceRequestDto()
        );

        return new RefreshServiceResponse(
            $refreshServiceResponse->getMessage(),
            $refreshServiceResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
