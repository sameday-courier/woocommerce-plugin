<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Validators\Awb\Generate;

use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Domain\Models\SamedayService;

if (!defined('ABSPATH')) {
    exit;
}

class GenerateAwbValidatorRequest
{
    /**
     * @var SamedayService $samedayService
     */
    public SamedayService $samedayService;

    /**
     * @var GenerateAwbItem $awbItem
     */
    public GenerateAwbItem $awbItem;

    public function __construct(
        SamedayService $samedayService,
        GenerateAwbItem $awbItem
    )
    {
        $this->samedayService = $samedayService;
        $this->awbItem = $awbItem;
    }
}
