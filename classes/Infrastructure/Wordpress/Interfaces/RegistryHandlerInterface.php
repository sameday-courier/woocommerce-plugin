<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces;

if (!defined( 'ABSPATH')) {
    exit;
}

interface RegistryHandlerInterface
{
    /**
     * @return void
     */
    public function register(): void;
}
