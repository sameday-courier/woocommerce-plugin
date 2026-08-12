<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbOrderSnapshot
{
    private int $orderId;

    private string $orderNumber;

    private float $orderTotal;

    private ?string $paymentMethodId;

    /**
     * @var array<string, mixed>
     */
    private array $shipping;

    /**
     * @var array<string, mixed>
     */
    private array $billing;

    /**
     * Opaque shipping-line collection for GenerateAwbItem (platform objects).
     *
     * @var array<int|string, mixed>
     */
    private array $shippingLines;

    private ?string $samedayServiceCode;

    /**
     * @var mixed
     */
    private $locker;

    /**
     * @param array<string, mixed> $shipping
     * @param array<string, mixed> $billing
     * @param array<int|string, mixed> $shippingLines
     * @param mixed $locker
     */
    public function __construct(
        int $orderId,
        string $orderNumber,
        float $orderTotal,
        ?string $paymentMethodId,
        array $shipping,
        array $billing,
        array $shippingLines,
        ?string $samedayServiceCode,
        $locker
    ) {
        $this->orderId = $orderId;
        $this->orderNumber = $orderNumber;
        $this->orderTotal = $orderTotal;
        $this->paymentMethodId = $paymentMethodId;
        $this->shipping = $shipping;
        $this->billing = $billing;
        $this->shippingLines = $shippingLines;
        $this->samedayServiceCode = $samedayServiceCode;
        $this->locker = $locker;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getOrderTotal(): float
    {
        return $this->orderTotal;
    }

    public function getPaymentMethodId(): ?string
    {
        return $this->paymentMethodId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getShipping(): array
    {
        return $this->shipping;
    }

    /**
     * @return array<string, mixed>
     */
    public function getBilling(): array
    {
        return $this->billing;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getShippingLines(): array
    {
        return $this->shippingLines;
    }

    public function getSamedayServiceCode(): ?string
    {
        return $this->samedayServiceCode;
    }

    /**
     * @return mixed
     */
    public function getLocker()
    {
        return $this->locker;
    }
}
