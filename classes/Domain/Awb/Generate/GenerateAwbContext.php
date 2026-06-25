<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate;

use SamedayCourier\Shipping\Domain\DTOs\BillingObject;
use SamedayCourier\Shipping\Domain\DTOs\ShippingObject;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbContext
{
    private int $orderId;

    private ShippingObject $shipping;

    private BillingObject $billing;

    private ?string $locker;

    private bool $hasOpenPackage;

    private bool $hasLockerFirstMile;

    private int $packageType;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $homeDeliveryAddress;

    /**
     * @param array<string, mixed>|null $homeDeliveryAddress
     */
    public function __construct(
        int $orderId,
        ShippingObject $shipping,
        BillingObject $billing,
        ?string $locker,
        bool $hasOpenPackage,
        bool $hasLockerFirstMile,
        int $packageType,
        ?array $homeDeliveryAddress = null
    ) {
        $this->orderId = $orderId;
        $this->shipping = $shipping;
        $this->billing = $billing;
        $this->locker = $locker;
        $this->hasOpenPackage = $hasOpenPackage;
        $this->hasLockerFirstMile = $hasLockerFirstMile;
        $this->packageType = $packageType;
        $this->homeDeliveryAddress = $homeDeliveryAddress;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getShipping(): ShippingObject
    {
        return $this->shipping;
    }

    public function getBilling(): BillingObject
    {
        return $this->billing;
    }

    public function getLocker(): ?string
    {
        return $this->locker;
    }

    public function hasOpenPackage(): bool
    {
        return $this->hasOpenPackage;
    }

    public function hasLockerFirstMile(): bool
    {
        return $this->hasLockerFirstMile;
    }

    public function getPackageType(): int
    {
        return $this->packageType;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getHomeDeliveryAddress(): ?array
    {
        return $this->homeDeliveryAddress;
    }
}
