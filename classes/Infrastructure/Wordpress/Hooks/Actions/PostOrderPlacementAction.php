<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Woo\Services\PostOrderPlacementHandler;

final class PostOrderPlacementAction extends AbstractAction
{
    private const ACTION = 'woocommerce_checkout_order_processed';

    private PostOrderPlacementHandler $postOrderPlacementHandler;

    /**
     * @param ?PostOrderPlacementHandler $postOrderPlacementHandler
     */
    public function __construct(
        ?PostOrderPlacementHandler $postOrderPlacementHandler = null
    ) {
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
        return ['orderId'];
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        if (null === $orderId = $args[0] ?? null) {
            return;
        }

        $this->postOrderPlacementHandler->handle((int) $orderId);
    }
}
