<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Blocks;

use SamedayCourier\Shipping\Domain\Ports\ChosenPaymentMethodReaderInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooChosenPaymentMethodReader;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;

/**
 * WooCommerce Checkout Blocks integration for COD repayment tax recalculation.
 *
 * Classic checkout uses open_package_script.js to trigger update_checkout on payment change.
 * Blocks checkout persists the active gateway through the Store API cart/extensions callback.
 */
final class RepaymentTaxBlocksIntegration extends AbstractBlocksIntegration
{
    public const NAME = 'sameday-repayment-tax-blocks';

    /**
     * Namespace used by extensionCartUpdate() and RegisterRepaymentTaxCartUpdateCallbackAction.
     */
    public const CART_UPDATE_NAMESPACE = 'sameday-repayment-tax';
    public const CART_UPDATE_FIELD = 'payment_method';

    private const SCRIPT_HANDLE = 'sameday-blocks-checkout-repayment-tax';
    private const SCRIPT = 'assets/js/blocks-checkout-repayment-tax.js';

    /**
     * @var CarrierSettingsServiceProvider $carrierSettingsServiceProvider
     */
    private CarrierSettingsServiceProvider $carrierSettingsServiceProvider;

    /**
     * @var ChosenPaymentMethodReaderInterface $chosenPaymentMethodReader
     */
    private ChosenPaymentMethodReaderInterface $chosenPaymentMethodReader;

    /**
     * @param CarrierSettingsServiceProvider|null $carrierSettingsServiceProvider
     * @param ChosenPaymentMethodReaderInterface|null $chosenPaymentMethodReader
     */
    public function __construct(
        ?CarrierSettingsServiceProvider $carrierSettingsServiceProvider = null,
        ?ChosenPaymentMethodReaderInterface $chosenPaymentMethodReader = null
    ) {
        $this->carrierSettingsServiceProvider = $carrierSettingsServiceProvider
            ?? new CarrierSettingsServiceProvider();
        $this->chosenPaymentMethodReader = $chosenPaymentMethodReader ?? new WooChosenPaymentMethodReader();
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
            ['wc-settings', 'wc-blocks-checkout', 'wp-data', self::HELPER_HANDLE]
        );
    }

    /**
     * @return string[]
     */
    public function get_script_handles(): array
    {
        if ($this->getRepaymentTax() <= 0) {
            return [];
        }

        return [self::SCRIPT_HANDLE];
    }

    /**
     * Exposed to JS as the `sameday-repayment-tax-blocks_data` setting.
     *
     * @return array<string, mixed>
     */
    public function get_script_data(): array
    {
        if (is_admin()) {
            return [];
        }

        if ($this->getRepaymentTax() <= 0) {
            return [];
        }

        $sessionPaymentMethod = $this->chosenPaymentMethodReader->getChosenPaymentMethod();

        return [
            'cartUpdateNamespace' => self::CART_UPDATE_NAMESPACE,
            'cartUpdateField' => self::CART_UPDATE_FIELD,
            'sessionPaymentMethod' => $sessionPaymentMethod ?? '',
        ];
    }

    /**
     * @return int
     */
    private function getRepaymentTax(): int
    {
        return $this->carrierSettingsServiceProvider->get()->getRepaymentTax() ?? 0;
    }
}
