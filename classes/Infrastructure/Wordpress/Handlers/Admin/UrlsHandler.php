<?php

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin;

class UrlsHandler
{
    /**
     * @param string $adminUrlPath
     * @param array $queryArgs
     *
     * @return string
     */
    public static function build(string $adminUrlPath = "admin.php", array $queryArgs = []): string
    {
        return add_query_arg(
            $queryArgs,
            admin_url($adminUrlPath)
        );
    }

    /**
     * @param string $adminUrlPath
     * @param array $queryArgs
     *
     * @return string
     */
    public static function buildEscaped(string $adminUrlPath = "admin.php", array $queryArgs = []): string
    {
        return esc_url(self::build($adminUrlPath, $queryArgs));
    }

    /**
     * @return string
     */
    public static function buildSamedaySettingsPage(): string
    {
        return self::build(
            'admin.php',
            [
                'page' => 'wc-settings',
                'tab' => 'shipping',
                'section' => 'samedaycourier',
            ]
        );
    }
}
