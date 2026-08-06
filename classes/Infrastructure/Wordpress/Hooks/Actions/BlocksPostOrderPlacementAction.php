<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

if (!defined('ABSPATH')) {
    exit;
}

final class BlocksPostOrderPlacementAction extends AbstractAction
{
    private const ACTION = 'woocommerce_blocks_checkout_order_processed';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @return string[]|null
     */
    public function getParams(): ?array
    {
        return ['order'];
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $order = $args[0] ?? null;
        if (!is_object($order) || !method_exists($order, 'get_id')) {
            return;
        }

        (new PostOrderPlacementAction())->handle($order->get_id());
    }
}
