<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface OrderStatusUpdaterInterface
{
    /**
     * @param int $orderId
     *
     * @return string|null
     */
    public function getStatus(int $orderId): ?string;

    /**
     * @param int $orderId
     * @param string $status
     * @param string $note
     *
     * @return bool
     */
    public function update(int $orderId, string $status, string $note = ''): bool;

    /**
     * Persist the current order status before AWB-driven status change.
     * Keeps an already stored value so the original pre-AWB status is preserved.
     *
     * @param int $orderId
     *
     * @return void
     */
    public function rememberStatusBeforeAwb(int $orderId): void;

    /**
     * Restore the status stored before AWB generation and clear the snapshot.
     *
     * @param int $orderId
     * @param string $note
     *
     * @return bool
     */
    public function restoreStatusBeforeAwb(int $orderId, string $note = ''): bool;
}
