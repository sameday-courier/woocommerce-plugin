<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Validators\Awb\Generate;

use SamedayCourier\Shipping\Domain\Models\CarrierPickupPoint;
use SamedayCourier\Shipping\Domain\Models\CarrierService;

class GenerateAwbValidatorRequest
{
    private int $orderId;

    private ?CarrierService $carrierService;

    private ?CarrierPickupPoint $pickupPoint;

    private ?string $destinationCountry;

    private ?string $recipientPhone;

    private ?string $recipientEmail;

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
     * @param ?string $destinationCountry
     * @param ?string $recipientPhone
     * @param ?string $recipientEmail
     * @param array $shippingLines
     * @param bool $hasExistingAwb
     * @param bool $hasParcels
     */
    public function __construct(
        int $orderId,
        ?CarrierService $carrierService,
        ?CarrierPickupPoint $pickupPoint,
        ?string $destinationCountry,
        ?string $recipientPhone,
        ?string $recipientEmail,
        array $shippingLines,
        bool $hasExistingAwb,
        bool $hasParcels
    ) {
        $this->orderId = $orderId;
        $this->carrierService = $carrierService;
        $this->pickupPoint = $pickupPoint;
        $this->destinationCountry = $destinationCountry;
        $this->recipientPhone = $recipientPhone;
        $this->recipientEmail = $recipientEmail;
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
     * @return ?string
     */
    public function getDestinationCountry(): ?string
    {
        return $this->destinationCountry;
    }

    /**
     * @return ?string
     */
    public function getRecipientPhone(): ?string
    {
        return $this->recipientPhone;
    }

    /**
     * @return ?string
     */
    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    /**
     * @return array<int, mixed>
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
