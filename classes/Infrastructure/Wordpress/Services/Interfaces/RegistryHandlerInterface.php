<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Interfaces;

if (!defined( 'ABSPATH')) {
    exit;
}

interface RegistryHandlerInterface
{
    /**
     * @return void
     */
    public static function register(): void;
}
