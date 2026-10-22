<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

use SamedayCourier\Shipping\Domain\Models\CarrierService;

final class OrderShippingChangesRequestDto
{
    private int $orderId;

    private CarrierService $service;

    /**
     * @var array<int, mixed>
     */
    private array $shippingLines;

    /**
     * @param int $orderId
     * @param CarrierService $service
     * @param array<int, mixed> $shippingLines
     */
    public function __construct(
        int $orderId,
        CarrierService $service,
        array $shippingLines
    ) {
        $this->orderId = $orderId;
        $this->service = $service;
        $this->shippingLines = $shippingLines;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return CarrierService
     */
    public function getService(): CarrierService
    {
        return $this->service;
    }

    /**
     * @return array<int, mixed>
     */
    public function getShippingLines(): array
    {
        return $this->shippingLines;
    }
}
