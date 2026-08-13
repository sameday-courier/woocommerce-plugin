<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderAwbProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters\AwbNumberColumnInWcOrderGrid;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use WC_Order;

final class ShowAwbNumberColumnInWcOrderGridAction extends AbstractAction
{
    private const ACTION = 'manage_woocommerce_page_wc-orders_custom_column';

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
        return ['column', 'order'];
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $column = $args[0] ?? null;
        $order = $args[1] ?? null;

        if (AwbNumberColumnInWcOrderGrid::COLUMN_KEY !== $column) {
            return;
        }

        if (!$order instanceof WC_Order) {
            return;
        }

        $awb = (new WooOrderAwbProvider(
            new SamedayAwbRepository()
        ))->get((int) $order->get_id());

        if (null === $awb) {
            return;
        }

        echo esc_html((string) $awb->getAwbNumber());
    }
}
