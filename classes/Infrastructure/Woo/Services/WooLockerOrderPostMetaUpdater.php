<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;

if (!defined('ABSPATH')) {
    exit;
}

final class WooLockerOrderPostMetaUpdater
{
    /**
     * @var WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater
     */
    private WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater;

    /**
     * @param WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater
     */
    public function __construct(WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater)
    {
        $this->wooOrderShippingAddressUpdater = $wooOrderShippingAddressUpdater;
    }

    /**
     * @param int $orderId
     *
     * @return void
     *
     * @throws JsonException
     */
    public function update(int $orderId): void
    {
        $this->wooOrderShippingAddressUpdater->activateOutOfHome($orderId);
    }
}
