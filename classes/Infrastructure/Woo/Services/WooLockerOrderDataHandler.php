<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostOrderMetaHandler;
use WC_Order;

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
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return;
        }

        PostOrderMetaHandler::update(
            $order,
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
            $locker
        );
    }
}
