<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Woo\Services\PostOrderPlacementHandler;

final class BlocksPostOrderPlacementAction extends AbstractAction
{
    private const ACTION = 'woocommerce_blocks_checkout_order_processed';

    private PostOrderPlacementHandler $postOrderPlacementHandler;

    /**
     * @param ?PostOrderPlacementHandler $postOrderPlacementHandler
     */
    public function __construct(?PostOrderPlacementHandler $postOrderPlacementHandler = null)
    {
        $this->postOrderPlacementHandler = $postOrderPlacementHandler ?? new PostOrderPlacementHandler();
    }

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
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        $order = $args[0] ?? null;
        if (!is_object($order) || !method_exists($order, 'get_id')) {
            return;
        }

        $this->postOrderPlacementHandler->handle((int) $order->get_id());
    }
}
