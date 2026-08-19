<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\OrderWeightCalculatorInterface;
use SamedayCourier\Shipping\Domain\Ports\WeightConverterInterface;
use WC_Order;
use WC_Order_Item_Product;
use WC_Product;

final class WooOrderWeightCalculator implements OrderWeightCalculatorInterface
{
    private const MIN_WEIGHT = 1.0;

    private WeightConverterInterface $weightConverter;

    /**
     * @param ?WeightConverterInterface $weightConverter
     */
    public function __construct(
        ?WeightConverterInterface $weightConverter = null
    ) {
        $this->weightConverter = $weightConverter ?? new WooWeightHandler();
    }

    /**
     * @param int $orderId
     *
     * @return array<int,
     */
    public function toPackageDimensions(int $orderId): array
    {
        return [
            1 => [
                'weight' => $this->calculateByOrderId($orderId),
            ],
        ];
    }

    /**
     * @param int $orderId
     *
     * @return float
     */
    public function calculateByOrderId(int $orderId): float
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return self::MIN_WEIGHT;
        }

        return $this->calculate($order);
    }

    /**
     * @param WC_Order $order
     *
     * @return float
     */
    public function calculate(WC_Order $order): float
    {
        $totalWeight = 0.0;

        foreach ($order->get_items() as $item) {
            if (!$item instanceof WC_Order_Item_Product) {
                continue;
            }

            $product = wc_get_product($item->get_product_id());
            $quantity = (float) $item->get_quantity();
            $weight = 0.0;

            if ($product instanceof WC_Product) {
                $weight = (float) $product->get_weight();
            }

            $totalWeight += (float) number_format($weight * $quantity, 2, '.', '');
        }

        $convertedWeight = $this->weightConverter->convert($totalWeight);

        return $convertedWeight > 0 ? $convertedWeight : self::MIN_WEIGHT;
    }
}
