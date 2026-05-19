<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Refresh;

if (!defined('ABSPATH')) {
    exit;
}

class RefreshServiceRequest
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
