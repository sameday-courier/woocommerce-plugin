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
     * @param int $orderId
     * @param string $orderNumber
     * @param float $orderTotal
     * @param ?string $paymentMethodId
     * @param array $shipping
     * @param array $billing
     * @param array $shippingLines
     * @param ?string $samedayServiceCode
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
     * @return array<string,
     */
    public function getShipping(): array
    {
        return $this->shipping;
    }

    /**
     * @return array<string,
     */
    public function getBilling(): array
    {
        return $this->billing;
    }

    /**
     * @return array<int|string,
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
}
