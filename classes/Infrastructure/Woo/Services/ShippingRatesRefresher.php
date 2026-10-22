<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;

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
        $cart = $woocommerce->cart;
        $session = $woocommerce->session;

        if (null === $cart || null === $session) {
            return;
        }

        foreach (array_keys($cart->get_shipping_packages()) as $packageKey) {
            $this->sessionHandler->set(
                CarrierSessionKeys::shippingForPackage((int) $packageKey),
                null
            );
        }

        $this->preserveChosenShippingMethod();
    }

    /**
     * @return void
     */
    public function preserveChosenShippingMethod(): void
    {
        $this->registerKeepChosenMethodFilter();
    }

    /**
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
