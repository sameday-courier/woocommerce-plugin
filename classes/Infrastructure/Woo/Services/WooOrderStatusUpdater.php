<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\OrderStatusUpdaterInterface;
use WC_Order;

final class WooOrderStatusUpdater implements OrderStatusUpdaterInterface
{
    /**
     * @param int $orderId
     * @param string $status
     * @param string $note
     *
     * @return bool
     */
    public function update(int $orderId, string $status, string $note = ''): bool
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return false;
        }

        $normalizedStatus = $this->normalizeStatus($status);
        if ('' === $normalizedStatus) {
            return false;
        }

        if ($order->get_status() === $normalizedStatus) {
            return true;
        }

        $order->update_status($normalizedStatus, $note);

        return true;
    }

    /**
     * @param string $status
     *
     * @return string
     */
    private function normalizeStatus(string $status): string
    {
        $status = trim($status);
        if (0 === strpos($status, 'wc-')) {
            $status = substr($status, 3);
        }

        return $status;
    }
}
