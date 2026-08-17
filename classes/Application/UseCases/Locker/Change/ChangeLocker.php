<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use Exception;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;

final class ChangeLocker
{
    private ChangeLockerItem $changeLockerItem;

    private LockerOrderDataHandlerInterface $lockerOrderDataHandler;

    /**
     * @param ChangeLockerRequest $changeLockerRequest
     */
    public function __construct(ChangeLockerRequest $changeLockerRequest)
    {
        $this->changeLockerItem = $changeLockerRequest->getChangeLockerItem();
        $this->lockerOrderDataHandler = $changeLockerRequest->getLockerOrderDataHandler();
    }

    /**
     * @return ChangeLockerResponse
     */
    public function execute(): ChangeLockerResponse
    {
        $orderId = $this->changeLockerItem->getOrderId();
        $locker = $this->changeLockerItem->getLocker();

        if ($orderId <= 0) {
            return new ChangeLockerResponse(
                'Invalid order id.',
                ResponseNoticeType::ERROR,
            );
        }

        if (null === $locker || '' === $locker) {
            return new ChangeLockerResponse(
                'Locker data is required.',
                ResponseNoticeType::ERROR,
            );
        }

        try {
            $this->lockerOrderDataHandler->add($orderId, $locker);
        } catch (Exception $exception) {
            return new ChangeLockerResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new ChangeLockerResponse(
            'Locker successfully updated.',
            ResponseNoticeType::SUCCESS,
        );
    }
}
