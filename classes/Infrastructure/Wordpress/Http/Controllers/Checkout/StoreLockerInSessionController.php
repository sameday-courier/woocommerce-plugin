<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout;

use SamedayCourier\Shipping\Infrastructure\Woo\Services\LockerSessionStore;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractNoPrivController;

final class StoreLockerInSessionController extends AbstractNoPrivController
{
    private const ACTION = 'store_sameday_locker_in_session';

    /**
     * @var LockerSessionStore $lockerSessionStore
     */
    private LockerSessionStore $lockerSessionStore;

    /**
     * @param LockerSessionStore|null $lockerSessionStore
     */
    public function __construct(
        ?LockerSessionStore $lockerSessionStore = null
    ) {
        $this->lockerSessionStore = $lockerSessionStore ?? new LockerSessionStore();
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array $inputParams
     *
     * @return void
     */
    protected function processNoPrivAction(array $inputParams): void
    {
        $this->lockerSessionStore->store($inputParams['locker'] ?? null);
    }
}
