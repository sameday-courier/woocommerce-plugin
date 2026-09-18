<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\Ports\OrderStatusUpdaterInterface;
use WC_Order;

final class WooOrderStatusUpdater implements OrderStatusUpdaterInterface
{
    /**
     * @param int $orderId
     *
     * @return string|null
     */
    public function getStatus(int $orderId): ?string
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return null;
        }

        $status = (string) $order->get_status();

        return '' !== $status ? $status : null;
    }

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
     * @param int $orderId
     *
     * @return void
     */
    public function rememberStatusBeforeAwb(int $orderId): void
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return;
        }

        $existing = $order->get_meta(CarrierConstants::POST_META_SAMEDAY_ORDER_STATUS_BEFORE_AWB, true);
        if (is_string($existing) && '' !== $existing) {
            return;
        }

        $currentStatus = (string) $order->get_status();
        if ('' === $currentStatus) {
            return;
        }

        $order->update_meta_data(
            CarrierConstants::POST_META_SAMEDAY_ORDER_STATUS_BEFORE_AWB,
            $currentStatus
        );
        $order->save();
    }

    /**
     * @param int $orderId
     * @param string $note
     *
     * @return bool
     */
    public function restoreStatusBeforeAwb(int $orderId, string $note = ''): bool
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return false;
        }

        $previousStatus = $order->get_meta(CarrierConstants::POST_META_SAMEDAY_ORDER_STATUS_BEFORE_AWB, true);
        if (!is_string($previousStatus) || '' === $previousStatus) {
            return false;
        }

        $order->delete_meta_data(CarrierConstants::POST_META_SAMEDAY_ORDER_STATUS_BEFORE_AWB);
        $order->save();

        return $this->update($orderId, $previousStatus, $note);
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
