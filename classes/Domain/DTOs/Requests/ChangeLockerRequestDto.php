<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class ChangeLockerRequestDto
{
    private int $orderId;

    /**
     * @var mixed $locker
     */
    private $locker;

    /**
     * @param mixed $locker
     */
    public function __construct(int $orderId, $locker)
    {
        $this->orderId = $orderId;
        $this->locker = $locker;
    }

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
