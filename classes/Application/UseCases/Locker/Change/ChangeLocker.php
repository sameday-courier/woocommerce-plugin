<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\ChangeLockerRequestDto;
use SamedayCourier\Shipping\Domain\Ports\ChangeLockerServiceProviderInterface;

final class ChangeLocker
{
    private ChangeLockerItem $changeLockerItem;

    private ChangeLockerServiceProviderInterface $changeLockerServiceProvider;

    /**
     * @param ChangeLockerRequest $changeLockerRequest
     */
    public function __construct(ChangeLockerRequest $changeLockerRequest)
    {
        $this->changeLockerItem = $changeLockerRequest->getChangeLockerItem();
        $this->changeLockerServiceProvider = $changeLockerRequest->getChangeLockerServiceProvider();
    }

    /**
     * @return ChangeLockerResponse
     */
    public function execute(): ChangeLockerResponse
    {
        $changeLockerResponse = $this->changeLockerServiceProvider->change(
            new ChangeLockerRequestDto(
                $this->changeLockerItem->getOrderId(),
                $this->changeLockerItem->getLocker()
            )
        );

        return new ChangeLockerResponse(
            $changeLockerResponse->getMessage(),
            $changeLockerResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
