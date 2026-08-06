<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Domain\Models\SamedayAwb;

if (!defined('ABSPATH')) {
    exit;
}

final class RemoveAwbRequest
{
    /**
     * @var SamedayAwb $awb
     */
    private SamedayAwb $awb;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    public function __construct(SamedayAwb $awb, AwbErrorParser $awbErrorParser)
    {
        $this->awb = $awb;
        $this->awbErrorParser = $awbErrorParser;
    }

    public function getAwb(): SamedayAwb
    {
        return $this->awb;
    }

    public function getAwbErrorParser(): AwbErrorParser
    {
        return $this->awbErrorParser;
    }
}
