<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces;

interface RegistryHandlerInterface
{
    /**
     * @return void
     */
    public function register(): void;
}
