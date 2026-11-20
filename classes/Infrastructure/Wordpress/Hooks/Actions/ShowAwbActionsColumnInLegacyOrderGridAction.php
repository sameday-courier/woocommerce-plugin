<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use WC_Order;

final class ShowAwbActionsColumnInLegacyOrderGridAction extends ShowAwbActionsColumnInWcOrderGridAction
{
    private const ACTION = 'manage_shop_order_posts_custom_column';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return ['column', 'postId'];
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $postId = (int) ($args[1] ?? 0);
        if ($postId <= 0) {
            return;
        }

        $order = wc_get_order($postId);

        if (!$order instanceof WC_Order) {
            return;
        }

        $this->renderColumn((string) ($args[0] ?? ''), $order);
    }
}
