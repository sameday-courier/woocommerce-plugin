<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;
use SamedayCourier\Shipping\Domain\Ports\LockerOrderDataHandlerInterface;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostMetaHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class WooLockerOrderDataHandler implements LockerOrderDataHandlerInterface
{
    /**
     * @var WooLockerOrderPostMetaUpdater $wooLockerOrderPostMetaUpdater
     */
    private WooLockerOrderPostMetaUpdater $wooLockerOrderPostMetaUpdater;

    /**
     * @param WooLockerOrderPostMetaUpdater $wooLockerOrderPostMetaUpdater
     */
    public function __construct(WooLockerOrderPostMetaUpdater $wooLockerOrderPostMetaUpdater)
    {
        $this->wooLockerOrderPostMetaUpdater = $wooLockerOrderPostMetaUpdater;
    }

    /**
     * @param int $orderId
     * @param mixed $locker
     *
     * @return void
     *
     * @throws JsonException
     */
    public function add(int $orderId, $locker): void
    {
        PostMetaHandler::update(
            $orderId,
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_LOCKER,
            $locker,
            false
        );

        $this->wooLockerOrderPostMetaUpdater->update($orderId);
    }
}
