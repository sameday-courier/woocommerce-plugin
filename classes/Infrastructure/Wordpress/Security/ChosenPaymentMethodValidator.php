<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

final class ChosenPaymentMethodValidator
{
    /**
     * Accept only payment gateway ids that WooCommerce exposes on checkout.
     *
     * @param mixed $paymentMethod
     *
     * @return string|null
     */
    public static function normalize($paymentMethod): ?string
    {
        if (!is_string($paymentMethod)) {
            return null;
        }

        $paymentMethod = sanitize_text_field($paymentMethod);
        if ('' === $paymentMethod) {
            return null;
        }

        if (!function_exists('WC')) {
            return null;
        }

        $woocommerce = WC();
        if (!method_exists($woocommerce, 'payment_gateways')) {
            return null;
        }

        $availableGateways = $woocommerce->payment_gateways()->get_available_payment_gateways();
        if (!isset($availableGateways[$paymentMethod])) {
            return null;
        }

        return $paymentMethod;
    }
}
