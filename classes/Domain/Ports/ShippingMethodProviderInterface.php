<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

if (!defined('ABSPATH')) {
    exit;
}

interface ShippingMethodProviderInterface
{
    /**
     * @return string
     */
    public function getChosenServiceCode(): string;
}
