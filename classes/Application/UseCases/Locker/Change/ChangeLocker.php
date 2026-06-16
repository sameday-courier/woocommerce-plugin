<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use Exception;
use JsonException;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Utils\Helper;

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
     * @param ChangeLockerRequest $changeLockerRequest
     */
    public function __construct(ChangeLockerRequest $changeLockerRequest)
    {
        $this->orderId = $changeLockerRequest->orderId;
        $this->locker = $changeLockerRequest->locker;
    }

    /**
     * @return ChangeLockerResponse
     */
    public function execute(): ChangeLockerResponse
    {
        if ($this->orderId <= 0) {
            return new ChangeLockerResponse(
                ResponseNoticeType::ERROR,
                'Invalid order id.',
            );
        }

        if (null === $this->locker || '' === $this->locker) {
            return new ChangeLockerResponse(
                ResponseNoticeType::ERROR,
                'Locker data is required.',
            );
        }

        try {
            Helper::addLockerToOrderData($this->orderId, $this->locker);
        } catch (JsonException|Exception $exception) {
            return new ChangeLockerResponse(
                ResponseNoticeType::ERROR,
                $exception->getMessage(),
            );
        }

        return new ChangeLockerResponse(
            ResponseNoticeType::SUCCESS,
            'Locker successfully updated.',
        );
    }
}
