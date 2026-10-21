<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class GenerateAwbOrderSnapshotDto
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
     * WooCommerce order-item IDs associated with the order.
     *
     * @var array<int, int>
     */
    private array $shippingLines;

    private ?string $samedayServiceCode;

    /**
     * @var mixed
     */
    private $locker;

    private bool $hasOpenPackage;

    /**
     * @var array<int, array{weight: float}>
     */
    private array $packageDimensions;

    /**
     * @param int $orderId
     * @param string $orderNumber
     * @param float $orderTotal
     * @param ?string $paymentMethodId
     * @param array $shipping
     * @param array $billing
     * @param array<int, int> $shippingLines
     * @param ?string $samedayServiceCode
     * @param mixed $locker
     * @param bool $hasOpenPackage
     * @param array<int, array{weight: float}> $packageDimensions
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
        $locker,
        bool $hasOpenPackage,
        array $packageDimensions
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
        $this->hasOpenPackage = $hasOpenPackage;
        $this->packageDimensions = $packageDimensions;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @return float
     */
    public function getOrderTotal(): float
    {
        return $this->orderTotal;
    }

    /**
     * @return ?string
     */
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
     * @return array<int, int>
     */
    public function getShippingLines(): array
    {
        return $this->shippingLines;
    }

    /**
     * @return ?string
     */
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

    /**
     * @return bool
     */
    public function hasOpenPackage(): bool
    {
        return $this->hasOpenPackage;
    }

    /**
     * @return array<int, array{weight: float}>
     */
    public function getPackageDimensions(): array
    {
        return $this->packageDimensions;
    }
}
