<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\CarrierSessionKeys;
use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\WooCommerceHandlerInterface;

final class WooSessionHandler implements SessionHandlerInterface
{
    /**
     * @var WooCommerceHandlerInterface $wooCommerceHandler
     */
    private WooCommerceHandlerInterface $wooCommerceHandler;

    /**
     * @param WooCommerceHandlerInterface|null $wooCommerceHandler
     */
    public function __construct(?WooCommerceHandlerInterface $wooCommerceHandler = null)
    {
        $this->wooCommerceHandler = $wooCommerceHandler ?? new WooHandler();
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $session = $this->resolveSession();
        if (null === $session) {
            return $default;
        }

        return $session->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $session = $this->resolveSession();
        if (null === $session) {
            return;
        }

        $session->set($key, $value);
    }

    /**
     * WooCommerce stores the checkout payment gateway id under chosen_payment_method.
     *
     * @return string|null
     */
    public function getChosenPaymentMethod(): ?string
    {
        $paymentMethod = $this->get(CarrierSessionKeys::CHOSEN_PAYMENT_METHOD);

        if (!is_string($paymentMethod) || '' === $paymentMethod) {
            return null;
        }

        return $paymentMethod;
    }

    /**
     * WooCommerce only boots the session for frontend requests, so it is absent
     * on admin-ajax until initialized. Initialize it when missing so a simple
     * session->set() works the same from classic and Blocks checkout.
     *
     * @return object|null
     */
    private function resolveSession(): ?object
    {
        $woocommerce = $this->wooCommerceHandler->getWC();

        if (
            (!isset($woocommerce->session) || !is_object($woocommerce->session))
            && method_exists($woocommerce, 'initialize_session')
        ) {
            $woocommerce->initialize_session();
        }

        if (!isset($woocommerce->session) || !is_object($woocommerce->session)) {
            return null;
        }

        return $woocommerce->session;
    }
}
