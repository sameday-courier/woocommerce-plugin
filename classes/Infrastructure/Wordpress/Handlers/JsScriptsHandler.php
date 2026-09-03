<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use InvalidArgumentException;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Domain\AllImportSteps;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\Models\CarrierCity;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\RegistryHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Security\NonceHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;

final class JsScriptsHandler implements RegistryHandlerInterface
{
    private const SCRIPT_PATH = 'path';
    private const SCRIPT_URL = 'url';
    private const SCRIPT_CONTEXT = 'context';
    private const SCRIPT_HANDLE = 'handle';
    private const SCRIPT_DEPS = 'deps';
    private const SCRIPT_IN_FOOTER = 'in_footer';
    private const CONTEXT_GROUP_ADMIN = 'admin';
    private const CONTEXT_GROUP_FRONTEND = 'frontend';
    private const LOCKER_PLUGIN_SDK_URL = CarrierConstants::LOCKER_PLUGIN_SDK_URL;
    private const LOCKER_SDK_HANDLE = 'sameday-lockerpluginsdk';

    /**
     * Available script load contexts.
     *
     * Use the array KEY as the 2nd argument of addScript() / addExternalScript().
     * Inline comments describe when each context loads.
     */
    private const WP_CONTEXT = [
        'admin_common' => 'admin_common',
        // Sameday admin pages (sameday_services, sameday_lockers, sameday_pickup_points),
        // Sameday WC settings section, or order admin (post.php / admin.php).
        'admin_full' => 'admin_full',
        // Sameday settings section (section=samedaycourier) or order admin pages (post.php / admin.php).
        'pickup_points' => 'pickup_points',
        // Pickup points admin page only (page=sameday_pickup_points).
        'checkout' => 'checkout',
        // Any WooCommerce checkout page (is_checkout()).
        'checkout_strict' => 'checkout_strict',
        // Checkout only, excluding order-pay and order-received.
        'checkout_classic' => 'checkout_classic',
        // Strict checkout rendered by the classic shortcode, not the Checkout block.
        'checkout_nomenclator' => 'checkout_nomenclator',
        // Checkout page when Sameday nomenclator is enabled.
        'admin_settings' => 'admin_settings',
        // Sameday WooCommerce shipping settings section only.
        'order_edit' => 'order_edit',
        // WooCommerce order edit screen only (classic shop_order or HPOS wc-orders).
        'orders_list' => 'orders_list',
        // WooCommerce orders list (HPOS wc-orders or classic shop_order list).
    ];

    /**
     * @var array|null
     */
    private static ?array $scripts = null;

    /**
     * @return void
     */
    public function register(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdmin']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueueFrontend'], 99999);
    }

    /**
     * @return void
     */
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

            self::enqueue(self::resolveHandle($handle, $script), $script);
        }
    }

    /**
     * @return void
     */
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
    /**
     * @return array
     */
    private static function getScripts(): array
    {
        if (null !== self::$scripts) {
            return self::$scripts;
        }

        self::$scripts = [
            'sameday-select2' => self::addScript(
                'select2',
                self::WP_CONTEXT['admin_full'],
                ['jquery'],
                false
            ),
            'sameday-lockerpluginsdk' => self::addExternalScript(
                self::LOCKER_PLUGIN_SDK_URL,
                self::WP_CONTEXT['order_edit']
            ),
            'sameday-lockers-sync-admin' => self::addScript(
                'lockers_sync_admin',
                self::WP_CONTEXT['admin_full'],
                ['jquery'],
                false
            ),
            'sameday-add-awb' => self::addScript(
                'add-awb',
                self::WP_CONTEXT['admin_full'],
                ['jquery'],
                false
            ),
            'sameday-admin-helper' => self::addScript(
                'helper',
                self::WP_CONTEXT['pickup_points']
            ),
            'sameday-admin-modal' => self::addScript(
                'sameday-admin-modal',
                self::WP_CONTEXT['admin_full'],
                ['jquery'],
                true
            ),
            'sameday-admin-modal-pickup' => self::withHandle(
                self::addScript(
                    'sameday-admin-modal',
                    self::WP_CONTEXT['pickup_points'],
                    ['jquery'],
                    true
                ),
                'sameday-admin-modal'
            ),
            'sameday-select2-pickup' => self::withHandle(
                self::addScript(
                    'select2',
                    self::WP_CONTEXT['pickup_points'],
                    ['jquery'],
                    false
                ),
                'sameday-select2'
            ),
            'sameday-admin-script' => self::addScript(
                'adminPickupPoints',
                self::WP_CONTEXT['pickup_points'],
                ['jquery', 'sameday-admin-modal', 'sameday-select2']
            ),
            'sameday-locker-plugin-checkout' => self::withHandle(
                self::addExternalScript(
                    self::LOCKER_PLUGIN_SDK_URL,
                    self::WP_CONTEXT['checkout_strict'],
                    [],
                    false
                ),
                self::LOCKER_SDK_HANDLE
            ),
            'sameday-helper' => self::addScript(
                'helper',
                self::WP_CONTEXT['checkout'],
                ['jquery'],
                true
            ),
            'sameday-lockers-script' => self::addScript(
                'lockers_sync',
                self::WP_CONTEXT['checkout_classic'],
                ['jquery', 'sameday-helper'],
                true
            ),
            'sameday-open-package-script' => self::addScript(
                'open_package_script',
                self::WP_CONTEXT['checkout_classic'],
                ['jquery', 'sameday-helper'],
                true
            ),
            'sameday-county-city-handle' => self::addScript(
                'county-city-handle',
                self::WP_CONTEXT['checkout_nomenclator'],
                // Reuse WooCommerce's registered `select2` on checkout to avoid loading a second copy.
                ['jquery', 'select2', 'sameday-helper'],
                false
            ),
            'sameday-settings-actions' => self::addScript(
                'sameday_settings_actions',
                self::WP_CONTEXT['admin_settings'],
                [],
                true
            ),
            'sameday-awb-history' => self::addScript(
                'awb_history',
                self::WP_CONTEXT['admin_full'],
                ['jquery'],
                false
            ),
            'sameday-awb-form' => self::addScript(
                'awb_form',
                self::WP_CONTEXT['admin_full'],
                ['jquery', 'sameday-select2'],
                false
            ),
            'sameday-bulk-awb' => self::addScript(
                'bulk-awb',
                self::WP_CONTEXT['orders_list'],
                ['jquery'],
                true
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
            case 'checkout_classic':
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
                return AdminPageValidatorHandler::isAdminCommonPage();
            case 'admin_full':
                return AdminPageValidatorHandler::isAdminFullPage();
            case 'pickup_points':
                return AdminPageValidatorHandler::isPickupPointsPage();
            case 'checkout':
                return FrontPageValidatorHandler::isCheckoutPage();
            case 'checkout_strict':
                return FrontPageValidatorHandler::isStrictCheckoutPage();
            case 'checkout_classic':
                return FrontPageValidatorHandler::isClassicCheckoutPage();
            case 'checkout_nomenclator':
                return FrontPageValidatorHandler::isCheckoutNomenclatorPage();
            case 'admin_settings':
                return AdminPageValidatorHandler::isSamedaySettingsPage();
            case 'order_edit':
                return AdminPageValidatorHandler::isOrderEditPage();
            case 'orders_list':
                return AdminPageValidatorHandler::isOrdersListPage();
            default:
                return false;
        }
    }

    /**
     * @param string $handle
     * @param array $script
     *
     * @return void
     */
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
    /**
     * @param string $handle
     *
     * @return void
     */
    private static function localizeScript(string $handle): void
    {
        switch ($handle) {
            case 'sameday-lockers-sync-admin':
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
                    'labels' => [
                        'chooseCounty' => TranslatorHandler::translate('Choose County'),
                        'selectCountyFirst' => TranslatorHandler::translate('First select a County'),
                        'pickCity' => TranslatorHandler::translate('Choose a City'),
                        'loading' => TranslatorHandler::translate('Loading...'),
                    ],
                ]);
                break;
            case 'sameday-helper':
                wp_localize_script($handle, 'samedayVars', [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonces' => [
                        'store_sameday_locker_in_session' => NonceHandler::createNonce(
                            'store_sameday_locker_in_session'
                        ),
                        'store_sameday_open_package_in_session' => NonceHandler::createNonce(
                            'store_sameday_open_package_in_session'
                        ),
                        'store_sameday_payment_method_in_session' => NonceHandler::createNonce(
                            'store_sameday_payment_method_in_session'
                        ),
                    ],
                ]);
                break;
            case 'sameday-lockers-script':
                $lockerSyncSettings = (new CarrierSettingsServiceProvider())->get();
                wp_localize_script($handle, 'samedayLockerSync', [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'action' => 'refresh_lockers_checkout',
                    'nonce' => NonceHandler::createNonce('refresh_lockers_checkout'),
                    'ttl' => CarrierConstants::LOCKERS_SYNC_TTL,
                    'ts' => $lockerSyncSettings->getSamedaySyncLockersTs(),
                    'useLockerMap' => $lockerSyncSettings->isLockersMapEnabled(),
                    'selectLockerText' => TranslatorHandler::translate('Select easyBox'),
                ]);
                break;
            case 'sameday-county-city-handle':
                wp_localize_script($handle, 'samedayCourierData', [
                    'cities' => self::getCitiesForCheckout(),
                ]);
                break;
            case 'sameday-bulk-awb':
                wp_localize_script($handle, 'samedayBulkAwb', [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'modes' => [
                        'generate' => [
                            'startAction' => 'bulk-generate-awb-start',
                            'nextAction' => 'bulk-generate-awb-next',
                            'startNonce' => NonceHandler::createNonce('bulk-generate-awb-start'),
                            'nextNonce' => NonceHandler::createNonce('bulk-generate-awb-next'),
                        ],
                        'remove' => [
                            'startAction' => 'bulk-remove-awb-start',
                            'nextAction' => 'bulk-remove-awb-next',
                            'startNonce' => NonceHandler::createNonce('bulk-remove-awb-start'),
                            'nextNonce' => NonceHandler::createNonce('bulk-remove-awb-next'),
                        ],
                    ],
                    'i18n' => [
                        'order' => TranslatorHandler::translate('Order'),
                        'processing' => TranslatorHandler::translate('Processing order #%1$s (%2$s/%3$s)'),
                        'genericError' => TranslatorHandler::translate('Something went wrong.'),
                    ],
                ]);
                break;
            case 'sameday-settings-actions':
                wp_localize_script($handle, 'samedayAllImport', [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'startAction' => 'all-import-start',
                    'nextAction' => 'all-import-next',
                    'startNonce' => NonceHandler::createNonce('all-import-start'),
                    'nextNonce' => NonceHandler::createNonce('all-import-next'),
                    'steps' => [
                        AllImportSteps::SERVICES => TranslatorHandler::translate('Services'),
                        AllImportSteps::PICKUP_POINTS => TranslatorHandler::translate('Pickup points'),
                        AllImportSteps::LOCKERS => TranslatorHandler::translate('Lockers'),
                        AllImportSteps::CITIES => TranslatorHandler::translate('Cities'),
                    ],
                    'i18n' => [
                        'processing' => TranslatorHandler::translate('Importing %s (%s/%s)'),
                        'complete' => TranslatorHandler::translate('Process complete, all data is imported.'),
                        'completedWithErrors' => TranslatorHandler::translate(
                            'Import process completed with errors. The following could not be imported: %s.'
                        ),
                        'genericError' => TranslatorHandler::translate('Something went wrong.'),
                    ],
                ]);
                break;
        }
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    private static function getCitiesForCheckout(): array
    {
        $cachedCities = (new SamedayCityRepository())->getCachedCities();

        return array_map(static function ($cityModels) {
            return array_map(
                static function ($city) {
                    if ($city instanceof CarrierCity) {
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
    /**
     * @param string $relativePath
     *
     * @return string
     */
    private static function getScriptUrl(string $relativePath): string
    {
        return AssetPathHandler::url($relativePath);
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    private static function getScriptVersion(string $relativePath): string
    {
        return AssetPathHandler::version($relativePath);
    }
}
