<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\BulkAwbModal;

if (!defined('ABSPATH')) {
    exit;
}

final class ShowBulkAwbButtonAction extends AbstractAction
{
    private const ACTION = 'admin_footer';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        if (!$this->isOrdersListPage()) {
            return;
        }

        echo BulkAwbModal::render();
    }

    /**
     * @return bool
     */
    private function isOrdersListPage(): bool
    {
        if (!is_admin()) {
            return false;
        }

        if (isset($_GET['page']) && 'wc-orders' === $_GET['page']) {
            $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash((string) $_GET['action'])) : '';

            return !in_array($action, ['edit', 'new'], true);
        }

        global $pagenow;

        return 'edit.php' === $pagenow
            && isset($_GET['post_type'])
            && 'shop_order' === $_GET['post_type'];
    }
}
