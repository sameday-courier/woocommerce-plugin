<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Domain\Models\SamedayAwb;

if (!defined('ABSPATH')) {
    exit;
}

class RemoveAwbRequest
{
    /**
     * @var SamedayAwb $awb
     */
    private SamedayAwb $awb;

    public function __construct(SamedayAwb $awb)
    {
        $this->awb = $awb;
    }

    public function getAwb(): SamedayAwb
    {
        return $this->awb;
    }
}
