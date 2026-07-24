<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class ChangeLockerRequest
{
    /**
     * @var int $orderId
     */
    public int $orderId;

    /**
     * @var mixed $locker
     */
    public $locker;

    /**
     * @var LockerOrderDataHandlerInterface $lockerOrderDataHandler
     */
    public LockerOrderDataHandlerInterface $lockerOrderDataHandler;

    /**
     * @param int $orderId
     * @param mixed $locker
     * @param LockerOrderDataHandlerInterface $lockerOrderDataHandler
     */
    public function __construct(int $orderId, $locker, LockerOrderDataHandlerInterface $lockerOrderDataHandler)
    {
        $this->orderId = $orderId;
        $this->locker = $locker;
        $this->lockerOrderDataHandler = $lockerOrderDataHandler;
    }
}
