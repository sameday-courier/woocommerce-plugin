<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use JsonException;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressArchive;
use WC_Order;

final class AdminOrderAddressUpdateAction extends AbstractAction
{
    private const ACTION = 'woocommerce_before_order_object_save';

    private const ADMIN_ORDER_SAVE_ACTION = 'woocommerce_process_shop_order_meta';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 10;
    }

    /**
     * @return string[]|null
     */
    public function getParams(): ?array
    {
        return ['order', 'dataStore'];
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        if (!doing_action(self::ADMIN_ORDER_SAVE_ACTION)) {
            return;
        }

        $order = $args[0] ?? null;
        if (!$order instanceof WC_Order) {
            return;
        }

        if (!$this->hasAddressChanges($order->get_changes())) {
            return;
        }

        try {
            $wpShippingAddressArchive = new WooOrderShippingAddressArchive();

            $wpShippingAddressArchive->updateHomeDeliverySnapshotFromOrder($order);
        } catch (JsonException $exception) {
            // Nothing has changed.
        }
    }

    /**
     * @param array<string, mixed> $changes
     *
     * @return bool
     */
    private function hasAddressChanges(array $changes): bool
    {
        if (isset($changes['billing']) || isset($changes['shipping'])) {
            return true;
        }

        foreach (array_keys($changes) as $changedProp) {
            if (
                0 === strpos((string) $changedProp, 'billing_')
                || 0 === strpos((string) $changedProp, 'shipping_')
            ) {
                return true;
            }
        }

        return false;
    }
}
