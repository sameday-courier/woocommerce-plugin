<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Blocks;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OpenPackageAvailabilityProvider;

/**
 * WooCommerce Checkout Blocks integration for the open package option.
 *
 * Classic checkout uses ShowOpenPackageFieldAction + open_package_script.js.
 * Blocks checkout has no PHP shipping template hooks, so the checkbox is injected via JS
 * and persisted through the Store API cart/extensions endpoint, which reprices the cart.
 */
final class OpenPackageBlocksIntegration extends AbstractBlocksIntegration
{
    public const NAME = 'sameday-open-package-blocks';

    /**
     * Namespace used by extensionCartUpdate() and RegisterOpenPackageCartUpdateCallbackAction.
     */
    public const CART_UPDATE_NAMESPACE = 'sameday-open-package';
    public const CART_UPDATE_FIELD = 'open_package';

    private const SCRIPT_HANDLE = 'sameday-blocks-checkout-open-package';
    private const SCRIPT = 'assets/js/blocks-checkout-open-package.js';

    /**
     * @var OpenPackageAvailabilityProvider $openPackageAvailabilityProvider
     */
    private OpenPackageAvailabilityProvider $openPackageAvailabilityProvider;

    /**
     * @var CarrierSettingsServiceProvider $carrierSettingsServiceProvider
     */
    private CarrierSettingsServiceProvider $carrierSettingsServiceProvider;

    /**
     * @var SessionHandlerInterface $sessionHandler
     */
    private SessionHandlerInterface $sessionHandler;

    /**
     * @param OpenPackageAvailabilityProvider|null $openPackageAvailabilityProvider
     * @param CarrierSettingsServiceProvider|null $carrierSettingsServiceProvider
     * @param SessionHandlerInterface|null $sessionHandler
     */
    public function __construct(
        ?OpenPackageAvailabilityProvider $openPackageAvailabilityProvider = null,
        ?CarrierSettingsServiceProvider $carrierSettingsServiceProvider = null,
        ?SessionHandlerInterface $sessionHandler = null
    ) {
        $this->openPackageAvailabilityProvider = $openPackageAvailabilityProvider
            ?? new OpenPackageAvailabilityProvider();
        $this->carrierSettingsServiceProvider = $carrierSettingsServiceProvider
            ?? new CarrierSettingsServiceProvider();
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return self::NAME;
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->registerHelperScript();

        $this->registerPluginScript(
            self::SCRIPT_HANDLE,
            self::SCRIPT,
            ['jquery', 'wc-settings', 'wc-blocks-checkout', self::HELPER_HANDLE]
        );
    }

    /**
     * @return string[]
     */
    public function get_script_handles(): array
    {
        return [self::SCRIPT_HANDLE];
    }

    /**
     * Exposed to JS as the `sameday-open-package-blocks_data` setting.
     *
     * @return array<string, mixed>
     */
    public function get_script_data(): array
    {
        if (is_admin()) {
            return [];
        }

        $serviceCodes = $this->openPackageAvailabilityProvider->supportedServiceCodes();
        if ([] === $serviceCodes) {
            return [];
        }

        $label = $this->carrierSettingsServiceProvider->get()->getOpenPackageLabel();

        return [
            'serviceCodes' => $serviceCodes,
            'label' => null !== $label && '' !== $label
                ? $label
                : TranslatorHandler::translate('Open package at delivery'),
            'checked' => 'yes' === $this->sessionHandler->get(CarrierSessionKeys::OPEN_PACKAGE),
            'pluginName' => CarrierConstants::PLUGIN_NAME,
            'cartUpdateNamespace' => self::CART_UPDATE_NAMESPACE,
            'cartUpdateField' => self::CART_UPDATE_FIELD,
        ];
    }
}
