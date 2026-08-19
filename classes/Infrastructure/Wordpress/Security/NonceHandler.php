<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

class NonceHandler
{
    /**
     * @param string $actionKey
     *
     * @return string
     */
    /**
     * @param string $actionKey
     *
     * @return string
     */
    public static function createNonce(string $actionKey): string
    {
        return esc_attr(wp_create_nonce($actionKey));
    }

    /**
     * @param string $nonceString
     * @param string $action
     *
     * @return bool
     */
    /**
     * @param string $nonceString
     * @param string $action
     *
     * @return bool
     */
    public static function verify(string $nonceString, string $action): bool
    {
        return (bool) wp_verify_nonce($nonceString, $action);
    }
}
