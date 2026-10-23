<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\DTOs\GenerateAwbOrderSnapshotDto;
use SamedayCourier\Shipping\Domain\Ports\GenerateAwbOrderProviderInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostOrderMetaHandler;
use WC_Order;
use WC_Payment_Gateway;

final class WooGenerateAwbOrderProvider implements GenerateAwbOrderProviderInterface
{
    private CarrierServiceFromShippingLineResolver $carrierServiceFromShippingLineResolver;

    /**
     * @param ?CarrierServiceFromShippingLineResolver $carrierServiceFromShippingLineResolver
     */
    public function __construct(
        ?CarrierServiceFromShippingLineResolver $carrierServiceFromShippingLineResolver = null
    ) {
        $this->carrierServiceFromShippingLineResolver = $carrierServiceFromShippingLineResolver
            ?? new CarrierServiceFromShippingLineResolver();
    }

    /**
     * @param int $orderId
     *
     * @return GenerateAwbOrderSnapshotDto|null
     */
    public function getById(int $orderId): ?GenerateAwbOrderSnapshotDto
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return null;
        }

        $shippingLines = $order->get_items('shipping');
        $paymentGateway = wc_get_payment_gateway_by_order($order);
        $service = $this->carrierServiceFromShippingLineResolver->resolve($shippingLines);

        return new GenerateAwbOrderSnapshotDto(
            $orderId,
            $order->get_order_number(),
            (float) $order->get_total(),
            $paymentGateway instanceof WC_Payment_Gateway ? $paymentGateway->id : null,
            $order->get_address('shipping'),
            $order->get_address(),
            $shippingLines,
            null !== $service ? $service->getSamedayCode() : null,
            PostOrderMetaHandler::get(
                $order,
                CarrierConstants::POST_META_SAMEDAY_SHIPPING_LOCKER
            ),
        );
    }
}
