<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Actions;

use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSessionHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingMethodProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\FrontPageValidatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OpenPackageAvailabilityProvider;

/**
 * Classic checkout only: woocommerce_review_order_after_shipping is not fired by the
 * Checkout block. OpenPackageBlocksIntegration covers Blocks-based checkouts.
 */
final class ShowOpenPackageFieldAction extends AbstractAction
{
    private const ACTION_NAME = 'woocommerce_review_order_after_shipping';

    /**
     * @var WooShippingMethodProvider $wooShippingMethodProvider
     */
    private WooShippingMethodProvider $wooShippingMethodProvider;

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
     * @param WooShippingMethodProvider|null $wooShippingMethodProvider
     * @param OpenPackageAvailabilityProvider|null $openPackageAvailabilityProvider
     * @param CarrierSettingsServiceProvider|null $carrierSettingsServiceProvider
     * @param SessionHandlerInterface|null $sessionHandler
     */
    public function __construct(
        ?WooShippingMethodProvider $wooShippingMethodProvider = null,
        ?OpenPackageAvailabilityProvider $openPackageAvailabilityProvider = null,
        ?CarrierSettingsServiceProvider $carrierSettingsServiceProvider = null,
        ?SessionHandlerInterface $sessionHandler = null
    ) {
        $this->wooShippingMethodProvider = $wooShippingMethodProvider ?? new WooShippingMethodProvider();
        $this->openPackageAvailabilityProvider = $openPackageAvailabilityProvider
            ?? new OpenPackageAvailabilityProvider();
        $this->carrierSettingsServiceProvider = $carrierSettingsServiceProvider
            ?? new CarrierSettingsServiceProvider();
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
    }

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return self::ACTION_NAME;
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     */
    public function handle(...$args): void
    {
        if (!FrontPageValidatorHandler::isCheckoutPage()) {
            return;
        }

        $isAvailable = $this->openPackageAvailabilityProvider->isAvailableForServiceCode(
            $this->wooShippingMethodProvider->getChosenServiceCode()
        );

        if (!$isAvailable) {
            return;
        }

        echo $this->buildHtmlContent();
    }

    /**
     * @return string
     */
    private function createNewField(): string
    {
        return woocommerce_form_field(
            'open_package',
            [
                'type' => 'checkbox',
                'class' => ['input-checkbox'],
                'id' => 'sameday_open_package',
                'label' => $this->carrierSettingsServiceProvider->get()->getOpenPackageLabel(),
                'required' => false,
                'return' => true,
            ],
            'yes' === $this->sessionHandler->get(CarrierSessionKeys::OPEN_PACKAGE) ? '1' : ''
        );
    }

    /**
     * @return string
     */
    private function buildHtmlContent(): string
    {
        return sprintf(
            '<tr class="shipping-pickup-store">
                <th></th>
                <td>
                    <ul id="shipping_method" class="woocommerce-shipping-methods sameday-shipping-methods">
                        <li>
                            %s
                        </li>
                    </ul>
                </td>
            </tr>',
            $this->createNewField()
        );
    }
}
