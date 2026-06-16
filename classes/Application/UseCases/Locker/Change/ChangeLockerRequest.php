<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

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
     * @param int $orderId
     * @param mixed $locker
     */
    public function __construct(int $orderId, $locker)
    {
        $this->orderId = $orderId;
        $this->locker = $locker;
    }
}
