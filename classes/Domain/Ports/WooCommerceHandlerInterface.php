<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

if (!defined('ABSPATH')) {
    exit;
}

interface WooCommerceHandlerInterface
{
    /**
     * @return object
     */
    public function getWC(): object;

    /**
     * @return string
     */
    public function getPlatformVersion(): string;

    /**
     * @return string
     */
    public function getPluginMainFile(): string;

    /**
     * @return string
     */
    public function getPluginVersion(): string;
}
