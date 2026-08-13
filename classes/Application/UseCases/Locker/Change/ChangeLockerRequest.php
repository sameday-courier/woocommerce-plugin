<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;

final class ChangeLockerRequest
{
    /**
     * @var ChangeLockerItem $changeLockerItem
     */
    private ChangeLockerItem $changeLockerItem;

    /**
     * @var LockerOrderDataHandlerInterface $lockerOrderDataHandler
     */
    private LockerOrderDataHandlerInterface $lockerOrderDataHandler;

    /**
     * @param ChangeLockerItem $changeLockerItem
     * @param LockerOrderDataHandlerInterface $lockerOrderDataHandler
     */
    public function __construct(
        ChangeLockerItem $changeLockerItem,
        LockerOrderDataHandlerInterface $lockerOrderDataHandler
    )
    {
        $this->changeLockerItem = $changeLockerItem;
        $this->lockerOrderDataHandler = $lockerOrderDataHandler;
    }

    /**
     * @return ChangeLockerItem
     */
    public function getChangeLockerItem(): ChangeLockerItem
    {
        return $this->changeLockerItem;
    }

    /**
     * @return LockerOrderDataHandlerInterface
     */
    public function getLockerOrderDataHandler(): LockerOrderDataHandlerInterface
    {
        return $this->lockerOrderDataHandler;
    }
}
