<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

if (!defined('ABSPATH')) {
    exit;
}

final class RenderAdminAwbFormsAction extends AbstractAction
{
    private const ACTION = 'admin_head';

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION;
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        echo $this->buildHtmlContent();
    }

    /**
     * @return string
     */
    private function buildHtmlContent(): string
    {
        return sprintf(
            '<form id="addAwbForm" method="POST" action="%1$s">
                <input type="hidden" name="action" value="add-awb">
                <input type="hidden" name="_wpnonce" value="%2$s">
            </form>
            <form id="showAsPdf" method="POST" action="%1$s">
                <input type="hidden" name="action" value="show-as-pdf">
                <input type="hidden" name="_wpnonce" value="%3$s">
            </form>
            <form id="addNewParcelForm" method="POST" action="%1$s">
                <input type="hidden" name="action" value="add-new-parcel">
                <input type="hidden" name="_wpnonce" value="%4$s">
            </form>
            <form id="removeAwb" method="POST" action="%1$s">
                <input type="hidden" name="action" value="remove-awb">
                <input type="hidden" name="_wpnonce" value="%5$s">
            </form>',
            admin_url('admin-post.php'),
            wp_create_nonce('add-awb'),
            wp_create_nonce('show-as-pdf'),
            wp_create_nonce('add-new-parcel'),
            wp_create_nonce('remove-awb')
        );
    }
}
