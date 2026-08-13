<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressArchiveInterface;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostMetaHandler;
use WC_Order;

final class WooOrderShippingAddressArchive implements OrderShippingAddressArchiveInterface
{
    /**
     * @param int $orderId
     *
     * @return void
     *
     * @throws JsonException
     */
    public function ensureHomeDeliverySnapshot(int $orderId): void
    {
        if ('' !== PostMetaHandler::get(
            $orderId,
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
            true
        )) {
            return;
        }

        $this->updateHomeDeliverySnapshot($orderId);
    }

    /**
     * @param int $orderId
     *
     * @return void
     *
     * @throws JsonException
     */
    public function updateHomeDeliverySnapshot(int $orderId): void
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return;
        }

        $this->updateHomeDeliverySnapshotFromOrder($order);
    }

    /**
     * @param WC_Order $order
     *
     * @return void
     *
     * @throws JsonException
     */
    public function updateHomeDeliverySnapshotFromOrder(WC_Order $order): void
    {
        PostMetaHandler::update(
            $order->get_id(),
            SamedayConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
            json_encode($this->buildSnapshotFromOrder($order), JSON_THROW_ON_ERROR),
            false
        );
    }

    /**
     * @param WC_Order $order
     *
     * @return array<string, string>
     */
    private function buildSnapshotFromOrder(WC_Order $order): array
    {
        return [
            '_shipping_first_name' => $order->get_shipping_first_name(),
            '_shipping_last_name' => $order->get_shipping_last_name(),
            '_shipping_company' => $order->get_shipping_company(),
            '_shipping_address_1' => $order->get_shipping_address_1(),
            '_shipping_address_2' => $order->get_shipping_address_2(),
            '_shipping_city' => $order->get_shipping_city(),
            '_shipping_state' => $order->get_shipping_state(),
            '_shipping_postcode' => $order->get_shipping_postcode(),
            '_shipping_country' => $order->get_shipping_country(),
            '_shipping_phone' => $order->get_shipping_phone(),
            '_billing_first_name' => $order->get_billing_first_name(),
            '_billing_last_name' => $order->get_billing_last_name(),
            '_billing_company' => $order->get_billing_company(),
            '_billing_address_1' => $order->get_billing_address_1(),
            '_billing_address_2' => $order->get_billing_address_2(),
            '_billing_city' => $order->get_billing_city(),
            '_billing_state' => $order->get_billing_state(),
            '_billing_postcode' => $order->get_billing_postcode(),
            '_billing_country' => $order->get_billing_country(),
            '_billing_phone' => $order->get_billing_phone(),
            '_billing_email' => $order->get_billing_email(),
        ];
    }
}
