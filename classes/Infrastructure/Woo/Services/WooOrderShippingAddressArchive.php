<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use JsonException;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressArchiveInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostOrderMetaHandler;
use WC_Order;

final class WooOrderShippingAddressArchive implements OrderShippingAddressArchiveInterface
{
    private const SNAPSHOT_FIELDS = [
        '_shipping_first_name',
        '_shipping_last_name',
        '_shipping_company',
        '_shipping_address_1',
        '_shipping_address_2',
        '_shipping_city',
        '_shipping_state',
        '_shipping_postcode',
        '_shipping_country',
        '_shipping_phone',
        '_billing_first_name',
        '_billing_last_name',
        '_billing_company',
        '_billing_address_1',
        '_billing_address_2',
        '_billing_city',
        '_billing_state',
        '_billing_postcode',
        '_billing_country',
        '_billing_phone',
        '_billing_email',
    ];

    /**
     * @param int $orderId
     *
     * @return void
     * @throws JsonException
     */
    public function refreshHomeDeliverySnapshot(int $orderId): void
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return;
        }

        $snapshot = PostOrderMetaHandler::get(
            $order,
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
            true
        );

        if ($this->isValidSnapshot($snapshot)) {
            return;
        }

        $this->updateHomeDeliverySnapshotFromOrder($order);
    }

    /**
     * @param mixed $snapshot
     *
     * @return bool
     */
    private function isValidSnapshot($snapshot): bool
    {
        if (!is_string($snapshot) || '' === $snapshot) {
            return false;
        }

        try {
            $decodedSnapshot = json_decode(
                $snapshot,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            return false;
        }

        if (!is_array($decodedSnapshot)) {
            return false;
        }

        foreach (self::SNAPSHOT_FIELDS as $field) {
            if (!array_key_exists($field, $decodedSnapshot) || !is_string($decodedSnapshot[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param WC_Order $order
     *
     * @return void
     * @throws JsonException
     */
    public function updateHomeDeliverySnapshotFromOrder(WC_Order $order): void
    {
        PostOrderMetaHandler::update(
            $order,
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_HD_ADDRESS,
            json_encode($this->buildSnapshotFromOrder($order), JSON_THROW_ON_ERROR)
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
