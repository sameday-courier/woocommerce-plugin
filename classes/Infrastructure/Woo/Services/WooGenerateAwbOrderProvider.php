<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\DTOs\GenerateAwbOrderSnapshot;
use SamedayCourier\Shipping\Domain\Ports\GenerateAwbOrderProviderInterface;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostMetaHandler;
use WC_Order;
use WC_Order_Item_Shipping;
use WC_Payment_Gateway;

final class WooGenerateAwbOrderProvider implements GenerateAwbOrderProviderInterface
{
    /**
     * @param int $orderId
     *
     * @return GenerateAwbOrderSnapshot|null
     */
    public function getById(int $orderId): ?GenerateAwbOrderSnapshot
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return null;
        }

        $shippingLines = $order->get_items('shipping');
        $paymentGateway = wc_get_payment_gateway_by_order($order);

        return new GenerateAwbOrderSnapshot(
            $orderId,
            $order->get_order_number(),
            (float) $order->get_total(),
            $paymentGateway instanceof WC_Payment_Gateway ? $paymentGateway->id : null,
            $order->get_address('shipping'),
            $order->get_address(),
            $shippingLines,
            $this->resolveSamedayServiceCode($shippingLines),
            PostMetaHandler::get(
                $orderId,
                SamedayConstants::POST_META_SAMEDAY_SHIPPING_LOCKER
            ),
        );
    }

    /**
     * @param array<int|string, mixed> $shippingLines
     */
    private function resolveSamedayServiceCode(array $shippingLines): ?string
    {
        foreach ($shippingLines as $shippingLine) {
            if (!$shippingLine instanceof WC_Order_Item_Shipping) {
                continue;
            }

            if ($shippingLine->get_method_id() !== SamedayConstants::PLUGIN_NAME) {
                continue;
            }

            $metaServiceCode = $shippingLine->get_meta('service_code');
            if (null !== $metaServiceCode && '' !== $metaServiceCode) {
                return (string) $metaServiceCode;
            }
        }

        return null;
    }
}
