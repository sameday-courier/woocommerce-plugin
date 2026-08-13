<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Loaders\Services;

use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\Locker\LockerInstance;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\PickupPoint\PickupPointInstance;
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Grid\Service\ServiceInstance;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\RegistryHandlerInterface;

class LoadersRegisterService implements RegistryHandlerInterface
{
    private const ACTION = 'plugins_loaded';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @return void
     */
    public function register(): void
    {
        ServiceInstance::get_instance();
        PickupPointInstance::get_instance();
        LockerInstance::get_instance();
    }
}
