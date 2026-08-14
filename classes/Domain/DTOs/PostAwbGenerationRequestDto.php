<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use Sameday\Objects\PostAwb\ParcelObject;
use SamedayCourier\Shipping\Domain\Models\CarrierService;

final class PostAwbGenerationRequestDto
{
    private int $orderId;

    /**
     * @var array<int, mixed> $shippingLines
     */
    private array $shippingLines;

    private CarrierService $service;

    private string $awbNumber;

    private float $awbCost;

    /**
     * @var ParcelObject[] $parcels
     */
    private array $parcels;

    /**
     * @param array<int, mixed> $shippingLines
     * @param ParcelObject[] $parcels
     */
    public function __construct(
        int $orderId,
        array $shippingLines,
        CarrierService $service,
        string $awbNumber,
        float $awbCost,
        array $parcels
    ) {
        $this->orderId = $orderId;
        $this->shippingLines = $shippingLines;
        $this->service = $service;
        $this->awbNumber = $awbNumber;
        $this->awbCost = $awbCost;
        $this->parcels = $parcels;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return array<int, mixed>
     */
    public function getShippingLines(): array
    {
        return $this->shippingLines;
    }

    public function getService(): CarrierService
    {
        return $this->service;
    }

    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }

    public function getAwbCost(): float
    {
        return $this->awbCost;
    }

    /**
     * @return ParcelObject[]
     */
    public function getParcels(): array
    {
        return $this->parcels;
    }
}
