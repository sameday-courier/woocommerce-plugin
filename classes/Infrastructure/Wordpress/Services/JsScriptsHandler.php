<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use InvalidArgumentException;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Domain\Models\SamedayCity;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedaySettings;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class JsScriptsHandler
{
    private const SCRIPT_PATH = 'path';
    private const SCRIPT_URL = 'url';
    private const SCRIPT_CONTEXT = 'context';
    private const SCRIPT_HANDLE = 'handle';
    private const SCRIPT_DEPS = 'deps';
    private const SCRIPT_IN_FOOTER = 'in_footer';

    private const CONTEXT_GROUP_ADMIN = 'admin';
    private const CONTEXT_GROUP_FRONTEND = 'frontend';

    private const LOCKER_PLUGIN_SDK_URL = 'https://cdn.sameday.ro/locker-plugin/lockerpluginsdk.js';

    /**
     * Available script load contexts.
     *
     * Use the array KEY as the 2nd argument of addScript() / addExternalScript().
     * Inline comments describe when each context loads.
     */
    private const WP_CONTEXT = [
        'admin_common' => 'admin_common', // Sameday admin pages (sameday_services, sameday_lockers, sameday_pickup_points), Sameday WC settings section, or order admin (post.php / admin.php).
        'admin_full' => 'admin_full', // Sameday settings section (section=samedaycourier) or order admin pages (post.php / admin.php).
        'pickup_points' => 'pickup_points', // Pickup points admin page only (page=sameday_pickup_points).
        'checkout' => 'checkout', // Any WooCommerce checkout page (is_checkout()).
        'checkout_strict' => 'checkout_strict', // Checkout only, excluding order-pay and order-received.
        'checkout_nomenclator' => 'checkout_nomenclator', // Checkout page when Sameday nomenclator is enabled.
        'admin_settings' => 'admin_settings', // Sameday WooCommerce shipping settings section only.
        'order_edit' => 'order_edit', // WooCommerce order edit screen only (classic shop_order or HPOS wc-orders).
    ];

    private const PLUGIN_ADMIN_PAGES = [
        'sameday_pickup_points',
        'sameday_lockers',
        'sameday_services',
    ];

    /**
     * @var array|null
     */
    private static ?array $scripts = null;

    /**
     * @return void
     */
    public static function register(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdmin']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueueFrontend'], 99999);
    }

    /**
     * @return void
     */
    public static function enqueueAdmin(): void
    {
        if (!is_admin()) {
            return;
        }

        foreach (self::getScripts() as $handle => $script) {
            if (self::CONTEXT_GROUP_ADMIN !== self::getContextGroup($script[self::SCRIPT_CONTEXT])) {
                continue;
            }

            if (!self::shouldEnqueue($script[self::SCRIPT_CONTEXT])) {
                continue;
            }

            if (self::WP_CONTEXT['pickup_points'] === $script[self::SCRIPT_CONTEXT]) {
                add_thickbox();
            }

            self::enqueue(self::resolveHandle($handle, $script), $script);
        }
    }

    /**
     * @return void
     */
    public static function enqueueFrontend(): void
    {
        foreach (self::getScripts() as $handle => $script) {
            if (self::CONTEXT_GROUP_FRONTEND !== self::getContextGroup($script[self::SCRIPT_CONTEXT])) {
                continue;
            }

            if (!self::shouldEnqueue($script[self::SCRIPT_CONTEXT])) {
                continue;
            }

            self::enqueue(self::resolveHandle($handle, $script), $script);
        }
    }

    /**
     * @return array
     */
    private static function getScripts(): array
    {
        if (null !== self::$scripts) {
            return self::$scripts;
        }

        self::$scripts = [
            'select2-script' => self::addScript(
                'select2',
                self::WP_CONTEXT['admin_full'],
                ['jquery'],
                false
            ),
            'lockerpluginsdk' => self::addExternalScript(
                self::LOCKER_PLUGIN_SDK_URL,
                self::WP_CONTEXT['order_edit']
            ),
            'lockers-sync-admin' => self::addScript(
                'lockers_sync_admin',
                self::WP_CONTEXT['admin_full'],
                ['jquery'],
                false
            ),
            'add-awb' => self::addScript(
                'add-awb',
                self::WP_CONTEXT['admin_full'],
                ['jquery'],
                false
            ),
            'sameday-admin-helper' => self::addScript(
                'helper',
                self::WP_CONTEXT['pickup_points']
            ),
            'sameday-admin-script' => self::addScript(
                'adminPickupPoints',
                self::WP_CONTEXT['pickup_points']
            ),
            'prod-locker-plugin' => self::addExternalScript(
                self::LOCKER_PLUGIN_SDK_URL,
                self::WP_CONTEXT['checkout_strict'],
                [],
                false
            ),
            'helper' => self::addScript(
                'helper',
                self::WP_CONTEXT['checkout_strict'],
                ['jquery'],
                true
            ),
            'lockers_script' => self::addScript(
                'lockers_sync',
                self::WP_CONTEXT['checkout_strict'],
                ['jquery'],
                true
            ),
            'open_package_script' => self::addScript(
                'open_package_script',
                self::WP_CONTEXT['checkout_strict'],
                ['jquery'],
                true
            ),
            'lockerpluginsdk_checkout' => self::withHandle(
                self::addExternalScript(
                    self::LOCKER_PLUGIN_SDK_URL,
                    self::WP_CONTEXT['checkout']
                ),
                'lockerpluginsdk'
            ),
            'custom-checkout-button' => self::addScript(
                'custom-checkout-button',
                self::WP_CONTEXT['checkout'],
                ['jquery'],
                true
            ),
            'county-city-handle' => self::addScript(
                'county-city-handle',
                self::WP_CONTEXT['checkout_nomenclator'],
                ['jquery', 'select2'],
                false
            ),
            'sameday-settings-actions' => self::addScript(
                'sameday_settings_actions',
                self::WP_CONTEXT['admin_settings'],
                [],
                false
            ),
            'awb-history' => self::addScript(
                'awb_history',
                self::WP_CONTEXT['admin_full'],
                ['jquery'],
                false
            ),
            'awb-form' => self::addScript(
                'awb_form',
                self::WP_CONTEXT['admin_full'],
                ['jquery', 'select2-script'],
                false
            ),
        ];

        return self::$scripts;
    }

    /**
     * @param string $fileName
     * @param string $context
     * @param array $deps
     * @param bool $inFooter
     *
     * @return array
     */
    private static function addScript(
        string $fileName,
        string $context,
        array $deps = ['jquery'],
        bool $inFooter = true
    ): array {
        if (!isset(self::WP_CONTEXT[$context])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown script context "%s". Available contexts: %s',
                    $context,
                    implode(', ', array_keys(self::WP_CONTEXT))
                )
            );
        }

        return [
            self::SCRIPT_PATH => sprintf('assets/js/%s.js', str_replace('.js', '', $fileName)),
            self::SCRIPT_CONTEXT => $context,
            self::SCRIPT_DEPS => $deps,
            self::SCRIPT_IN_FOOTER => $inFooter,
        ];
    }

    /**
     * @param string $url
     * @param string $context
     * @param array $deps
     * @param bool $inFooter
     *
     * @return array
     */
    private static function addExternalScript(
        string $url,
        string $context,
        array $deps = ['jquery'],
        bool $inFooter = false
    ): array {
        if (!isset(self::WP_CONTEXT[$context])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown script context "%s". Available contexts: %s',
                    $context,
                    implode(', ', array_keys(self::WP_CONTEXT))
                )
            );
        }

        return [
            self::SCRIPT_URL => $url,
            self::SCRIPT_CONTEXT => $context,
            self::SCRIPT_DEPS => $deps,
            self::SCRIPT_IN_FOOTER => $inFooter,
        ];
    }

    /**
     * @param array $script
     * @param string $handle
     *
     * @return array
     */
    private static function withHandle(array $script, string $handle): array
    {
        $script[self::SCRIPT_HANDLE] = $handle;

        return $script;
    }

    /**
     * @param string $registryKey
     * @param array $script
     *
     * @return string
     */
    private static function resolveHandle(string $registryKey, array $script): string
    {
        return $script[self::SCRIPT_HANDLE] ?? $registryKey;
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
            case 'checkout_nomenclator':
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
            case 'checkout_nomenclator':
                return is_checkout() && SamedaySettings::isUseSamedayNomenclator();
            case 'admin_settings':
                return self::isSamedaySettingsPage();
            case 'order_edit':
                return self::isOrderEditPage();
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
    private static function isOrderEditPage(): bool
    {
        global $pagenow;

        if ('post.php' === $pagenow && isset($_GET['post'])) {
            return 'shop_order' === get_post_type((int) $_GET['post']);
        }

        if ('admin.php' === $pagenow
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
     * @param array $script
     *
     * @return void
     */
    private static function enqueue(string $handle, array $script): void
    {
        $deps = $script[self::SCRIPT_DEPS];
        $inFooter = $script[self::SCRIPT_IN_FOOTER];
        $version = null;

        if (isset($script[self::SCRIPT_URL])) {
            wp_enqueue_script($handle, $script[self::SCRIPT_URL], $deps, $version, $inFooter);
        } else {
            $relativePath = $script[self::SCRIPT_PATH];
            wp_enqueue_script(
                $handle,
                self::getScriptUrl($relativePath),
                $deps,
                self::getScriptVersion($relativePath),
                $inFooter
            );
        }

        self::localizeScript($handle);
    }

    /**
     * @param string $handle
     *
     * @return void
     */
    private static function localizeScript(string $handle): void
    {
        switch ($handle) {
            case 'lockers-sync-admin':
                wp_localize_script($handle, 'samedayLockerAdmin', [
                    'nonces' => [
                        'change_locker' => wp_create_nonce('change_locker'),
                    ],
                ]);
                break;
            case 'sameday-admin-script':
                wp_localize_script($handle, 'samedayPickupPointsAdmin', [
                    'nonces' => [
                        'get_counties' => wp_create_nonce('get_counties'),
                        'get_cities' => wp_create_nonce('get_cities'),
                        'send_pickup_point' => wp_create_nonce('send_pickup_point'),
                        'delete_pickup_point' => wp_create_nonce('delete_pickup_point'),
                    ],
                ]);
                break;
            case 'helper':
                wp_localize_script($handle, 'samedayVars', [
                    'samedayNonce' => wp_create_nonce('sameday-post-data'),
                ]);
                break;
            case 'county-city-handle':
                wp_localize_script($handle, 'samedayCourierData', [
                    'cities' => self::getCitiesForCheckout(),
                ]);
                break;
            case 'custom-checkout-button':
                wp_localize_script($handle, 'samedayData', [
                    'username' => SamedaySettings::getUser(),
                    'country' => SamedaySettings::getHostCountry(),
                    'buttonText' => TranslatorHandler::translate('Show Locations Map'),
                ]);
                break;
        }
    }

    /**
     * @return array
     */
    private static function getCitiesForCheckout(): array
    {
        $cachedCities = (new SamedayCityRepository())->getCachedCities();

        return array_map(static function ($cityModels) {
            return array_map(
                static function ($city) {
                    if ($city instanceof SamedayCity) {
                        return $city->toArray();
                    }

                    return [
                        'city_name' => $city['city_name'] ?? null,
                        'county_code' => $city['county_code'] ?? null,
                    ];
                },
                $cityModels
            );
        }, $cachedCities);
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    private static function getScriptUrl(string $relativePath): string
    {
        return plugins_url($relativePath, WooHandler::getPluginMainFile());
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    private static function getScriptVersion(string $relativePath): string
    {
        $absolutePath = SAMEDAYCOURIER_SHIPPING_PLUGIN_PATH . $relativePath;

        if (file_exists($absolutePath)) {
            return (string) filemtime($absolutePath);
        }

        return WooHandler::getPluginVersion();
    }
}
