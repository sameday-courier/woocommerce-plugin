<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;

/**
 * Sameday shipping costs depend on session state (open package, payment method) that is not
 * part of WooCommerce's package hash, so cached rates survive changes that should reprice them.
 *
 * Storing null under `shipping_for_package_{key}` unsets the cached rates entry so WC_Shipping
 * recalculates on the next totals pass (e.g. CartExtensionsSchema::calculate_totals).
 */
final class ShippingRatesRefresher
{
    /**
     * @var bool
     */
    private static bool $keepChosenMethodFilterRegistered = false;

    /**
     * @var WooHandler $wooCommerceHandler
     */
    private WooHandler $wooCommerceHandler;

    /**
     * @var SessionHandlerInterface $sessionHandler
     */
    private SessionHandlerInterface $sessionHandler;

    /**
     * @param WooHandler|null $wooCommerceHandler
     * @param SessionHandlerInterface|null $sessionHandler
     */
    public function __construct(
        ?WooHandler $wooCommerceHandler = null,
        ?SessionHandlerInterface $sessionHandler = null
    ) {
        $this->wooCommerceHandler = $wooCommerceHandler ?? new WooHandler();
        $this->sessionHandler = $sessionHandler ?? new WooSessionHandler();
    }

    /**
     * @return void
     */
    public function refresh(): void
    {
        $woocommerce = $this->wooCommerceHandler->getWC();

        if (
            !isset($woocommerce->cart, $woocommerce->session)
            || !is_object($woocommerce->cart)
            || !is_object($woocommerce->session)
        ) {
            return;
        }

        foreach (array_keys($woocommerce->cart->get_shipping_packages()) as $packageKey) {
            // Unset the cached rates entry. Storing `false` also works for WC_Shipping's
            // is_array() check, but leaving a permanent false key pollutes the session blob.
            $this->sessionHandler->set(
                CarrierSessionKeys::shippingForPackage((int) $packageKey),
                null
            );
        }

        // Do not call calculate_shipping() here. Store API cart/extensions already recalculates
        // totals after the callback; a nested pass only amplifies session races and API estimates.
        $this->preserveChosenShippingMethod();
    }

    /**
     * Keep the customer's current shipping rate across the next totals recalculation in this request.
     *
     * Useful for Store API cart/extensions callbacks that do not reprice shipping but still trigger
     * WC_Cart::calculate_totals() afterward.
     *
     * @return void
     */
    public function preserveChosenShippingMethod(): void
    {
        $this->registerKeepChosenMethodFilter();
    }

    /**
     * WC_Cart::calculate_shipping() re-evaluates the chosen rate for every package and falls back to
     * the default one whenever `shipping_method_counts` is out of sync - which it always is on Blocks
     * checkout, since nothing there populates that session key. Repricing would therefore drop the
     * customer on another shipping option right after they tick an option such as open package.
     *
     * The filter stays for the rest of the request so the CartExtensions totals pass is covered.
     *
     * @return void
     */
    private function registerKeepChosenMethodFilter(): void
    {
        if (self::$keepChosenMethodFilterRegistered) {
            return;
        }

        self::$keepChosenMethodFilterRegistered = true;

        add_filter(
            'woocommerce_shipping_chosen_method',
            static function ($default, $rates, $chosenMethod) {
                if (
                    is_string($chosenMethod)
                    && '' !== $chosenMethod
                    && is_array($rates)
                    && isset($rates[$chosenMethod])
                ) {
                    return $chosenMethod;
                }

                return $default;
            },
            10,
            3
        );
    }
}
