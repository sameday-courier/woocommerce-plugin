<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

final class ChangeLockerRequest implements RequestInterface
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
     * @param int $orderId
     * @param mixed $locker
     */
    public function __construct(int $orderId, $locker)
    {
        $this->orderId = $orderId;
        $this->locker = $locker;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return mixed
     */
    public function getLocker()
    {
        return $this->locker;
    }
}
