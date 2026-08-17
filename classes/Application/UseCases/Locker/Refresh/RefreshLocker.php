<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RefreshLockerRequestDto;
use SamedayCourier\Shipping\Domain\Ports\RefreshLockerServiceProviderInterface;

final class RefreshLocker
{
    private RefreshLockerServiceProviderInterface $refreshLockerServiceProvider;

    /**
     * @param RefreshLockerRequest $refreshLockerRequest
     */
    public function __construct(RefreshLockerRequest $refreshLockerRequest)
    {
        $this->refreshLockerServiceProvider = $refreshLockerRequest->getRefreshLockerServiceProvider();
    }

    /**
     * @return RefreshLockerResponse
     */
    public function execute(): RefreshLockerResponse
    {
        $refreshLockerResponse = $this->refreshLockerServiceProvider->refresh(
            new RefreshLockerRequestDto()
        );

        return new RefreshLockerResponse(
            $refreshLockerResponse->getMessage(),
            $refreshLockerResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
