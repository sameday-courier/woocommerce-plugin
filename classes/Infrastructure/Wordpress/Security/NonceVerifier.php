<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Security;

if (!defined('ABSPATH')) {
    exit;
}

class NonceVerifier
{
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
