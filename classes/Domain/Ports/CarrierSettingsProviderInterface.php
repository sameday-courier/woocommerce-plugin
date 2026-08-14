<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\CarrierSettings;

interface CarrierSettingsProviderInterface
{
    /**
     * @return CarrierSettings
     */
    public function get(): CarrierSettings;

    /**
     * @param int $timestamp
     *
     * @return void
     */
    public function setSamedaySyncLockersTs(int $timestamp): void;
}
