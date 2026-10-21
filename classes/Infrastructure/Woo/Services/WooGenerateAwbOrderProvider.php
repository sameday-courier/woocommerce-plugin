<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\DTOs\GenerateAwbOrderSnapshotDto;
use SamedayCourier\Shipping\Domain\Ports\GenerateAwbOrderProviderInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\PostOrderMetaHandler;
use WC_Order;
use WC_Order_Item_Shipping;
use WC_Payment_Gateway;

final class WooGenerateAwbOrderProvider implements GenerateAwbOrderProviderInterface
{
    private WooOrderWeightCalculator $orderWeightCalculator;

    /**
     * @param WooOrderWeightCalculator|null $orderWeightCalculator
     */
    public function __construct(?WooOrderWeightCalculator $orderWeightCalculator = null)
    {
        $this->orderWeightCalculator = $orderWeightCalculator ?? new WooOrderWeightCalculator();
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

        $locker = PostOrderMetaHandler::get(
            $order,
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_LOCKER
        );

        $isOpenPackage = (bool) PostOrderMetaHandler::get(
            $order,
            CarrierConstants::POST_META_SAMEDAY_SHIPPING_OPEN_PACKAGE_OPTION
        );

        return new GenerateAwbOrderSnapshotDto(
            $order->get_id(),
            $order->get_order_number(),
            $order->get_total(),
            $paymentGateway instanceof WC_Payment_Gateway ? $paymentGateway->id : null,
            $order->get_address('shipping'),
            $order->get_address(),
            $this->resolveShippingLineIds($shippingLines),
            $this->resolveSamedayServiceCode($shippingLines),
            $locker,
            $isOpenPackage,
            $this->orderWeightCalculator->toPackageDimensionsFromOrder($order),
        );
    }

    /**
     * @param array<int, mixed> $shippingLines
     *
     * @return ?string
     */
    private function resolveSamedayServiceCode(array $shippingLines): ?string
    {
        foreach ($shippingLines as $shippingLine) {
            if (!$shippingLine instanceof WC_Order_Item_Shipping) {
                continue;
            }

            if ($shippingLine->get_method_id() !== CarrierConstants::PLUGIN_NAME) {
                continue;
            }

            $metaServiceCode = $shippingLine->get_meta('service_code');
            if (null !== $metaServiceCode && '' !== $metaServiceCode) {
                return (string) $metaServiceCode;
            }
        }

        return null;
    }

    /**
     * @param array<int, mixed> $shippingLines
     *
     * @return array<int, int>
     */
    private function resolveShippingLineIds(array $shippingLines): array
    {
        $shippingLineIds = [];

        foreach ($shippingLines as $shippingLine) {
            if ($shippingLine instanceof WC_Order_Item_Shipping) {
                $shippingLineIds[] = $shippingLine->get_id();
            }
        }

        return $shippingLineIds;
    }
}
