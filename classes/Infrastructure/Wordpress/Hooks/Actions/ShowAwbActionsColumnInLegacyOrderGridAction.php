<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

/**
 * Renders the "Awb actions" column cell on the legacy (posts-based) shop_order list.
 *
 * Unlike the HPOS hook, WordPress passes (column, post_id) here, so the order is
 * resolved through the WooCommerce CRUD API before delegating to the shared renderer.
 */
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

        $this->renderColumn((string) ($args[0] ?? ''), wc_get_order($postId));
    }
}
