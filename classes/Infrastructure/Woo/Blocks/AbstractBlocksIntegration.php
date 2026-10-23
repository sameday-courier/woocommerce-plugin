<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Blocks;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\AssetPathHandler;

abstract class AbstractBlocksIntegration implements IntegrationInterface
{
    protected const HELPER_HANDLE = 'sameday-helper';
    protected const HELPER_SCRIPT = 'assets/js/helper.js';

    /**
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
