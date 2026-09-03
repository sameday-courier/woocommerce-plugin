<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;

final class ChangeLockerRequest implements RequestInterface
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var LockerDto|null $locker
     */
    private ?LockerDto $locker;

    /**
     * @param int $orderId
     * @param LockerDto|null $locker
     */
    public function __construct(int $orderId, ?LockerDto $locker)
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
     * @return LockerDto|null
     */
    public function getLocker(): ?LockerDto
    {
        return $this->locker;
    }
}
