<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;

/**
 * Sameday shipping costs depend on session state (open package, payment method) that is not
 * part of WooCommerce's package hash, so cached rates survive changes that should reprice them.
 *
 * Storing a non-array under `shipping_for_package_{key}` makes WC_Shipping treat the cache as
 * missing and recalculate.
 */
final class ShippingRatesRefresher
{
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
            $this->sessionHandler->set(
                CarrierSessionKeys::shippingForPackage((int) $packageKey),
                false
            );
        }

        $keepChosenMethod = $this->buildKeepChosenMethodFilter();

        add_filter('woocommerce_shipping_chosen_method', $keepChosenMethod, 10, 3);

        try {
            $woocommerce->cart->calculate_shipping();
        } finally {
            remove_filter('woocommerce_shipping_chosen_method', $keepChosenMethod, 10);
        }
    }

    /**
     * WC_Cart::calculate_shipping() re-evaluates the chosen rate for every package and falls back to
     * the default one whenever `shipping_method_counts` is out of sync - which it always is on Blocks
     * checkout, since nothing there populates that session key. Repricing would therefore drop the
     * customer on another shipping option right after they tick an option such as open package.
     *
     * @return callable
     */
    private function buildKeepChosenMethodFilter(): callable
    {
        return static function ($default, $rates, $chosenMethod) {
            if (is_string($chosenMethod) && '' !== $chosenMethod && is_array($rates) && isset($rates[$chosenMethod])) {
                return $chosenMethod;
            }

            return $default;
        };
    }
}
