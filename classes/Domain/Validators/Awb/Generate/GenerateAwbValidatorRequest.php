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

    /**
     * @param int $orderId
     * @param ?CarrierService $carrierService
     * @param ?CarrierPickupPoint $pickupPoint
     * @param BillingDto $billing
     * @param array $shippingLines
     * @param bool $hasExistingAwb
     * @param bool $hasParcels
     */
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

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return ?CarrierService
     */
    public function getCarrierService(): ?CarrierService
    {
        return $this->carrierService;
    }

    /**
     * @return ?CarrierPickupPoint
     */
    public function getPickupPoint(): ?CarrierPickupPoint
    {
        return $this->pickupPoint;
    }

    /**
     * @return BillingDto
     */
    public function getBilling(): BillingDto
    {
        return $this->billing;
    }

    /**
     * @return array<int,
     */
    public function getShippingLines(): array
    {
        return $this->shippingLines;
    }

    /**
     * @return bool
     */
    public function hasExistingAwb(): bool
    {
        return $this->hasExistingAwb;
    }

    /**
     * @return bool
     */
    public function hasParcels(): bool
    {
        return $this->hasParcels;
    }
}
