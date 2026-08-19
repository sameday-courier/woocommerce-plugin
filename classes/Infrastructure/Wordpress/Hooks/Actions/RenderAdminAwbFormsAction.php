<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Infrastructure\Common\Services\HtmlHandler;

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
     * @param mixed ...$args
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
        return HtmlHandler::buildHtml('admin-awb-forms', [
            'actionUrl' => admin_url('admin-post.php'),
            'addAwbNonce' => wp_create_nonce('add-awb'),
            'showAsPdfNonce' => wp_create_nonce('show-as-pdf'),
            'addNewParcelNonce' => wp_create_nonce('add-new-parcel'),
            'removeAwbNonce' => wp_create_nonce('remove-awb'),
        ]);
    }
}
