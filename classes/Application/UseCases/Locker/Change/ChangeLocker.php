<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use Exception;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class ChangeLocker
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var mixed $locker
     */
    private $locker;

    /**
     * @var LockerOrderDataHandlerInterface $lockerOrderDataHandler
     */
    private LockerOrderDataHandlerInterface $lockerOrderDataHandler;

    /**
     * @param ChangeLockerRequest $changeLockerRequest
     */
    public function __construct(ChangeLockerRequest $changeLockerRequest)
    {
        $this->orderId = $changeLockerRequest->orderId;
        $this->locker = $changeLockerRequest->locker;
        $this->lockerOrderDataHandler = $changeLockerRequest->lockerOrderDataHandler;
    }

    /**
     * @return ChangeLockerResponse
     */
    public function execute(): ChangeLockerResponse
    {
        if ($this->orderId <= 0) {
            return new ChangeLockerResponse(
                'Invalid order id.',
                ResponseNoticeType::ERROR,
            );
        }

        if (null === $this->locker || '' === $this->locker) {
            return new ChangeLockerResponse(
                'Locker data is required.',
                ResponseNoticeType::ERROR,
            );
        }

        try {
            $this->lockerOrderDataHandler->add($this->orderId, $this->locker);
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
