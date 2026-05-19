<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

if (!defined('ABSPATH')) {
    exit;
}

class RefreshPickupPointRequest
{
    /**
     * @var bool $hasSamedayOptions
     */
    private bool $hasSamedayOptions;

    /**
     * @param bool $hasSamedayOptions
     */
    public function __construct(bool $hasSamedayOptions)
    {
        $this->hasSamedayOptions = $hasSamedayOptions;
    }

    /**
     * @return bool
     */
    public function hasSamedayOptions(): bool
    {
        return $this->hasSamedayOptions;
    }
}
