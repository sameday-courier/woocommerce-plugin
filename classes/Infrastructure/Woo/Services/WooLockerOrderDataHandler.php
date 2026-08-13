<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostMetaHandler;

final class WooLockerOrderDataHandler implements LockerOrderDataHandlerInterface
{
    /**
     * @param int $orderId
     * @param mixed $locker
     *
     * @return void
     */
    public function add(int $orderId, $locker): void
    {
        PostMetaHandler::update(
            $orderId,
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
            $locker,
            false
        );
    }
}
