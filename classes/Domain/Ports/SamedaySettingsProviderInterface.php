<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\SamedaySettings;

interface SamedaySettingsProviderInterface
{
    /**
     * @return SamedaySettings
     */
    public function get(): SamedaySettings;

    /**
     * @param int $timestamp
     *
     * @return void
     */
    public function setSamedaySyncLockersTs(int $timestamp): void;
}
