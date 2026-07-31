<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters;

if (!defined('ABSPATH')) {
    exit;
}

final class AddPluginRowMetaFilter extends AbstractFilter
{
    private const FILTER = 'plugin_row_meta';
    private const PLUGIN_FILE = 'samedaycourier-shipping.php';

    /**
     * @return string
     */
    public function getFilterName(): string
    {
        return self::FILTER;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 10;
    }

    /**
     * @return string[]|null
     */
    public function getParams(): ?array
    {
        return ['links', 'pluginFileName', 'pluginData', 'status'];
    }

    /**
     * @param mixed ...$args
     *
     * @return array
     */
    public function handle(...$args): array
    {
        $links = $args[0] ?? [];
        $pluginFileName = $args[1] ?? '';

        if (!is_array($links)) {
            $links = [];
        }

        if (strpos((string) $pluginFileName, self::PLUGIN_FILE)) {
            $pathToSettings = admin_url() . 'admin.php?page=wc-settings&tab=shipping&section=samedaycourier';
            $pathToEawb = 'https://eawb.sameday.ro/';
            $links[] = '<a href="' . esc_html__($pathToSettings, 'woocommerce') . '" target="_blank"> ' . esc_html__('Settings', 'woocommerce') . ' </a>';
            $links[] = '<a href="' . esc_html__($pathToEawb, 'woocommerce') . '" target="_blank"> ' . esc_html__('eAWB', 'woocommerce') . ' </a>';
        }

        return $links;
    }
}
