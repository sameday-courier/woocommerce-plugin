<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

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
     * @var array<int, array{position: int, awbNumber: string}>
     */
    private array $parcels;

    /**
     * @param int $orderId
     * @param array $shippingLines
     * @param CarrierService $service
     * @param string $awbNumber
     * @param float $awbCost
     * @param array $parcels
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

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return array<int,
     */
    public function getShippingLines(): array
    {
        return $this->shippingLines;
    }

    /**
     * @return CarrierService
     */
    public function getService(): CarrierService
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }

    /**
     * @return float
     */
    public function getAwbCost(): float
    {
        return $this->awbCost;
    }

    /**
     * @return array<int,
     */
    public function getParcels(): array
    {
        return $this->parcels;
    }
}
