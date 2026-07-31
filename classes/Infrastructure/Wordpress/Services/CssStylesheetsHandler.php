<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use InvalidArgumentException;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Interfaces\RegistryHandlerInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class CssStylesheetsHandler implements RegistryHandlerInterface
{
    private const STYLE_PATH = 'path';
    private const STYLE_CONTEXT = 'context';

    private const CONTEXT_GROUP_ADMIN = 'admin';
    private const CONTEXT_GROUP_FRONTEND = 'frontend';

    /**
     * Available stylesheet load contexts.
     *
     * Use the array KEY as the 2nd argument of addStyleSheet().
     * Inline comments describe when each context loads.
     */
    private const WP_CONTEXT = [
        'admin_common' => 'admin_common', // Sameday admin pages (sameday_services, sameday_lockers, sameday_pickup_points), Sameday WC settings section, or order admin (post.php / admin.php).
        'admin_full' => 'admin_full', // Sameday settings section (section=samedaycourier) or order admin pages (post.php / admin.php).
        'pickup_points' => 'pickup_points', // Pickup points admin page only (page=sameday_pickup_points).
        'checkout' => 'checkout', // Any WooCommerce checkout page (is_checkout()).
        'checkout_strict' => 'checkout_strict', // Checkout only, excluding order-pay and order-received.
    ];

    private const PLUGIN_ADMIN_PAGES = [
        'sameday_pickup_points',
        'sameday_lockers',
        'sameday_services',
    ];

    /**
     * @var array|null
     */
    private static ?array $styles = null;

    /**
     * @return void
     */
    public static function register(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdmin']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueueFrontend']);
    }

    /**
     * @return void
     */
    public static function enqueueAdmin(): void
    {
        if (!is_admin()) {
            return;
        }

        foreach (self::getStyles() as $handle => $style) {
            if (self::CONTEXT_GROUP_ADMIN !== self::getContextGroup($style[self::STYLE_CONTEXT])) {
                continue;
            }

            if (!self::shouldEnqueue($style[self::STYLE_CONTEXT])) {
                continue;
            }

            self::enqueue($handle, $style[self::STYLE_PATH]);
        }
    }

    /**
     * @return void
     */
    public static function enqueueFrontend(): void
    {
        foreach (self::getStyles() as $handle => $style) {
            if (self::CONTEXT_GROUP_FRONTEND !== self::getContextGroup($style[self::STYLE_CONTEXT])) {
                continue;
            }

            if (!self::shouldEnqueue($style[self::STYLE_CONTEXT])) {
                continue;
            }

            self::enqueue($handle, $style[self::STYLE_PATH]);
        }
    }

    /**
     * @return array
     */
    private static function getStyles(): array
    {
        if (null !== self::$styles) {
            return self::$styles;
        }

        self::$styles = [
            'sameday-admin-button-style' => self::addStyleSheet(
                'sameday_admin_button',
                self::WP_CONTEXT['admin_common']
            ),
            'sameday-admin-style' => self::addStyleSheet(
                'sameday_admin',
                self::WP_CONTEXT['admin_full']
            ),
            'select2-style' => self::addStyleSheet(
                'select2',
                self::WP_CONTEXT['admin_full']
            ),
            'sameday-thickboxform-style' => self::addStyleSheet(
                'tickbox-form',
                self::WP_CONTEXT['pickup_points']
            ),
            'sameday-locker-checkout-style' => self::addStyleSheet(
                'sameday_locker_checkout',
                self::WP_CONTEXT['checkout_strict']
            ),
            'sameday-front-button-style' => self::addStyleSheet(
                'sameday_front_button',
                self::WP_CONTEXT['checkout']
            ),
        ];

        return self::$styles;
    }

    /**
     * @param string $fileName
     * @param string $context
     *
     * @return array
     */
    private static function addStyleSheet(string $fileName, string $context): array
    {
        if (!isset(self::WP_CONTEXT[$context])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown stylesheet context "%s". Available contexts: %s',
                    $context,
                    implode(', ', array_keys(self::WP_CONTEXT))
                )
            );
        }

        return [
            self::STYLE_PATH => sprintf('assets/css/%s.css', str_replace('.css', '', $fileName)),
            self::STYLE_CONTEXT => $context,
        ];
    }

    /**
     * @param string $context
     *
     * @return string
     */
    private static function getContextGroup(string $context): string
    {
        switch ($context) {
            case 'checkout':
            case 'checkout_strict':
                return self::CONTEXT_GROUP_FRONTEND;
            default:
                return self::CONTEXT_GROUP_ADMIN;
        }
    }

    /**
     * @param string $context
     *
     * @return bool
     */
    private static function shouldEnqueue(string $context): bool
    {
        if (!isset(self::WP_CONTEXT[$context])) {
            return false;
        }

        switch ($context) {
            case 'admin_common':
                return self::isAdminCommonPage();
            case 'admin_full':
                return self::isAdminFullPage();
            case 'pickup_points':
                return self::isPickupPointsPage();
            case 'checkout':
                return is_checkout();
            case 'checkout_strict':
                return self::isStrictCheckoutPage();
            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    private static function isPluginAdminPage(): bool
    {
        return isset($_GET['page'])
            && in_array($_GET['page'], self::PLUGIN_ADMIN_PAGES, true);
    }

    /**
     * @return bool
     */
    private static function isSamedaySettingsPage(): bool
    {
        return isset($_GET['page'], $_GET['tab'], $_GET['section'])
            && 'wc-settings' === $_GET['page']
            && 'shipping' === $_GET['tab']
            && SamedayConstants::PLUGIN_NAME === $_GET['section'];
    }

    /**
     * @return bool
     */
    private static function isOrderAdminPage(): bool
    {
        global $pagenow;

        return 'post.php' === $pagenow || 'admin.php' === $pagenow;
    }

    /**
     * @return bool
     */
    private static function isAdminCommonPage(): bool
    {
        return self::isPluginAdminPage()
            || self::isSamedaySettingsPage()
            || self::isOrderAdminPage();
    }

    /**
     * @return bool
     */
    private static function isAdminFullPage(): bool
    {
        $section = $_GET['section'] ?? null;

        if (SamedayConstants::PLUGIN_NAME === $section) {
            return true;
        }

        return self::isOrderAdminPage();
    }

    /**
     * @return bool
     */
    private static function isPickupPointsPage(): bool
    {
        return isset($_GET['page']) && 'sameday_pickup_points' === $_GET['page'];
    }

    /**
     * @return bool
     */
    private static function isStrictCheckoutPage(): bool
    {
        global $wp;

        return is_checkout()
            && empty($wp->query_vars['order-pay'])
            && !isset($wp->query_vars['order-received']);
    }

    /**
     * @param string $handle
     * @param string $relativePath
     *
     * @return void
     */
    private static function enqueue(string $handle, string $relativePath): void
    {
        wp_enqueue_style(
            $handle,
            self::getStyleUrl($relativePath),
            [],
            self::getStyleVersion($relativePath)
        );
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    private static function getStyleUrl(string $relativePath): string
    {
        return plugins_url($relativePath, WooHandler::getPluginMainFile());
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    private static function getStyleVersion(string $relativePath): string
    {
        $absolutePath = SAMEDAYCOURIER_SHIPPING_PLUGIN_PATH . $relativePath;

        if (file_exists($absolutePath)) {
            return (string) filemtime($absolutePath);
        }

        return WooHandler::getPluginVersion();
    }
}
