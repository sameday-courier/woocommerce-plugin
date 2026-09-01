<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use Exception;
use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;

final class ChangeLocker
{
    /**
     * @var LockerOrderDataHandlerInterface $lockerOrderDataHandler
     */
    private LockerOrderDataHandlerInterface $lockerOrderDataHandler;

    /**
     * @param LockerOrderDataHandlerInterface $lockerOrderDataHandler
     */
    public function __construct(
        LockerOrderDataHandlerInterface $lockerOrderDataHandler
    ) {
        $this->lockerOrderDataHandler = $lockerOrderDataHandler;
    }

    /**
     * @param ChangeLockerRequest $request
     * @return ChangeLockerResponse
     */
    public function execute(ChangeLockerRequest $request): ChangeLockerResponse
    {
        $orderId = $request->getOrderId();
        $locker = $request->getLocker();

        if ($orderId <= 0) {
            return new ChangeLockerResponse(
                'Invalid order id.',
                true
            );
        }

        if (null === $locker || '' === $locker) {
            return new ChangeLockerResponse(
                'Locker data is required.',
                true
            );
        }

        try {
            $this->lockerOrderDataHandler->add($orderId, $locker);
        } catch (Exception $exception) {
            return new ChangeLockerResponse(
                $exception->getMessage(),
                true
            );
        }

        return new ChangeLockerResponse(
            'Locker successfully updated.',
            false
        );
    }
}
