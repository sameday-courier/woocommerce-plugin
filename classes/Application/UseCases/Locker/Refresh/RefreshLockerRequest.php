<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

if (!defined('ABSPATH')) {
    exit;
}

class RefreshLockerRequest
{
    /**
     * @var bool $hasSamedayOptions
     */
    private bool $hasSamedayOptions;

    /**
     * @var bool $silentOnApiError
     */
    private bool $silentOnApiError;

    /**
     * @param bool $hasSamedayOptions
     * @param bool $silentOnApiError
     */
    public function __construct(bool $hasSamedayOptions, bool $silentOnApiError = false)
    {
        $this->hasSamedayOptions = $hasSamedayOptions;
        $this->silentOnApiError = $silentOnApiError;
    }

    /**
     * @return bool
     */
    public function hasSamedayOptions(): bool
    {
        return $this->hasSamedayOptions;
    }

    /**
     * @return bool
     */
    public function isSilentOnApiError(): bool
    {
        return $this->silentOnApiError;
    }
}
