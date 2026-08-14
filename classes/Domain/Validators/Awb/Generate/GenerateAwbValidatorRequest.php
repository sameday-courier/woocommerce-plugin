<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Validators\Awb\Generate;

use SamedayCourier\Shipping\Domain\DTOs\BillingDto;
use SamedayCourier\Shipping\Domain\Models\CarrierPickupPoint;
use SamedayCourier\Shipping\Domain\Models\CarrierService;

class GenerateAwbValidatorRequest
{
    private int $orderId;

    private ?CarrierService $carrierService;

    private ?CarrierPickupPoint $pickupPoint;

    private BillingDto $billing;

    /**
     * @var array<int, mixed> $shippingLines
     */
    private array $shippingLines;

    private bool $hasExistingAwb;

    private bool $hasParcels;

    public function __construct(
        int $orderId,
        ?CarrierService $carrierService,
        ?CarrierPickupPoint $pickupPoint,
        BillingDto $billing,
        array $shippingLines,
        bool $hasExistingAwb,
        bool $hasParcels
    ) {
        $this->orderId = $orderId;
        $this->carrierService = $carrierService;
        $this->pickupPoint = $pickupPoint;
        $this->billing = $billing;
        $this->shippingLines = $shippingLines;
        $this->hasExistingAwb = $hasExistingAwb;
        $this->hasParcels = $hasParcels;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getCarrierService(): ?CarrierService
    {
        return $this->carrierService;
    }

    public function getPickupPoint(): ?CarrierPickupPoint
    {
        return $this->pickupPoint;
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

    public function hasExistingAwb(): bool
    {
        return $this->hasExistingAwb;
    }

    public function hasParcels(): bool
    {
        return $this->hasParcels;
    }
}
