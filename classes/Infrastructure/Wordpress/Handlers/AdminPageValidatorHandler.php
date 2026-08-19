<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use SamedayCourier\Shipping\Domain\CarrierConstants;

final class AdminPageValidatorHandler
{
    private const PLUGIN_ADMIN_PAGES = [
        'sameday_pickup_points',
        'sameday_lockers',
        'sameday_services',
    ];

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isPluginAdminPage(): bool
    {
        return isset($_GET['page'])
            && in_array($_GET['page'], self::PLUGIN_ADMIN_PAGES, true);
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isSamedaySettingsPage(): bool
    {
        return isset($_GET['page'], $_GET['tab'], $_GET['section'])
            && 'wc-settings' === $_GET['page']
            && 'shipping' === $_GET['tab']
            && CarrierConstants::PLUGIN_NAME === $_GET['section'];
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isOrderAdminPage(): bool
    {
        global $pagenow;

        return 'post.php' === $pagenow || 'admin.php' === $pagenow;
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isOrderEditPage(): bool
    {
        global $pagenow;

        if ('post.php' === $pagenow && isset($_GET['post'])) {
            return 'shop_order' === get_post_type((int)$_GET['post']);
        }

        if (
            'admin.php' === $pagenow
            && isset($_GET['page'], $_GET['action'])
            && 'wc-orders' === $_GET['page']
            && 'edit' === $_GET['action']
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isOrdersListPage(): bool
    {
        if (!is_admin()) {
            return false;
        }

        if (isset($_GET['page']) && 'wc-orders' === $_GET['page']) {
            $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash((string)$_GET['action'])) : '';

            return !in_array($action, ['edit', 'new'], true);
        }

        global $pagenow;

        return 'edit.php' === $pagenow
            && isset($_GET['post_type'])
            && 'shop_order' === $_GET['post_type'];
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isAdminCommonPage(): bool
    {
        return self::isPluginAdminPage()
            || self::isSamedaySettingsPage()
            || self::isOrderAdminPage();
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isAdminFullPage(): bool
    {
        $section = $_GET['section'] ?? null;

        if (CarrierConstants::PLUGIN_NAME === $section) {
            return true;
        }

        return self::isOrderAdminPage();
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public static function isPickupPointsPage(): bool
    {
        return isset($_GET['page']) && 'sameday_pickup_points' === $_GET['page'];
    }
}
