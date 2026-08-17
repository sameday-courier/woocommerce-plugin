<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Change;

use SamedayCourier\Shipping\Domain\Ports\ChangeLockerServiceProviderInterface;

final class ChangeLockerRequest
{
    private ChangeLockerItem $changeLockerItem;

    private ChangeLockerServiceProviderInterface $changeLockerServiceProvider;

    /**
     * @param ChangeLockerItem $changeLockerItem
     * @param ChangeLockerServiceProviderInterface $changeLockerServiceProvider
     */
    public function __construct(
        ChangeLockerItem $changeLockerItem,
        ChangeLockerServiceProviderInterface $changeLockerServiceProvider
    ) {
        $this->changeLockerItem = $changeLockerItem;
        $this->changeLockerServiceProvider = $changeLockerServiceProvider;
    }

    /**
     * @return ChangeLockerItem
     */
    public function getChangeLockerItem(): ChangeLockerItem
    {
        return $this->changeLockerItem;
    }

    /**
     * @return ChangeLockerServiceProviderInterface
     */
    public function getChangeLockerServiceProvider(): ChangeLockerServiceProviderInterface
    {
        return $this->changeLockerServiceProvider;
    }
}
