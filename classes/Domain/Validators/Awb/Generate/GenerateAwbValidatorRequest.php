<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Validators\Awb\Generate;

use SamedayCourier\Shipping\Domain\DTOs\BillingDto;
use SamedayCourier\Shipping\Domain\Models\SamedayService;

if (!defined('ABSPATH')) {
    exit;
}

class GenerateAwbValidatorRequest
{
    private ?SamedayService $samedayService;

    private BillingDto $billing;

    /**
     * @var array<int, mixed> $shippingLines
     */
    private array $shippingLines;

    public function __construct(
        ?SamedayService $samedayService,
        BillingDto $billing,
        array $shippingLines
    )
    {
        $this->samedayService = $samedayService;
        $this->billing = $billing;
        $this->shippingLines = $shippingLines;
    }

    public function getSamedayService(): ?SamedayService
    {
        return $this->samedayService;
    }

    public function getBilling(): BillingDto
    {
        return $this->billing;
    }

    /**
     * @return array<int, mixed>
     */
    public function getShippingLines(): array
    {
        return $this->shippingLines;
    }
}
