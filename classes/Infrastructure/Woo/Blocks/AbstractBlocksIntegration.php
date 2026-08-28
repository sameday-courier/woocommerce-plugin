<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Blocks;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\AssetPathHandler;

/**
 * Shared plumbing for Sameday Checkout Blocks integrations.
 *
 * `initialize()` runs on every request (WooCommerce registers block types on `init`), so
 * implementations must only register handles there and resolve data in `get_script_data()`,
 * which WooCommerce calls lazily when the block is rendered.
 */
abstract class AbstractBlocksIntegration implements IntegrationInterface
{
    /**
     * Shared with JsScriptsHandler so helper.js is never loaded twice.
     */
    protected const HELPER_HANDLE = 'sameday-helper';
    protected const HELPER_SCRIPT = 'assets/js/helper.js';

    /**
     * Sameday blocks are rendered by plain JS, so there is nothing to load in the editor.
     *
     * @return string[]
     */
    public function get_editor_script_handles(): array
    {
        return [];
    }

    /**
     * @return void
     */
    protected function registerHelperScript(): void
    {
        $this->registerPluginScript(self::HELPER_HANDLE, self::HELPER_SCRIPT, ['jquery']);
    }

    /**
     * @param string $handle
     * @param string $relativePath
     * @param string[] $deps
     *
     * @return void
     */
    protected function registerPluginScript(string $handle, string $relativePath, array $deps): void
    {
        $this->registerScript(
            $handle,
            AssetPathHandler::url($relativePath),
            $deps,
            AssetPathHandler::version($relativePath)
        );
    }

    /**
     * @param string $handle
     * @param string $src
     * @param string[] $deps
     * @param string|null $version
     * @param bool $inFooter
     *
     * @return void
     */
    protected function registerScript(
        string $handle,
        string $src,
        array $deps,
        ?string $version,
        bool $inFooter = true
    ): void {
        if (wp_script_is($handle, 'registered')) {
            return;
        }

        wp_register_script($handle, $src, $deps, $version, $inFooter);
    }
}
