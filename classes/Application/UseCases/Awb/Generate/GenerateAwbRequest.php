<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\DTOs\BillingDto;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\DTOs\ShippingDto;

final class GenerateAwbRequest implements RequestInterface
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var int $serviceId
     */
    private int $serviceId;

    /**
     * @var int $pickupPointId
     */
    private int $pickupPointId;

    /**
     * @var array<int, mixed> $shippingLines
     */
    private array $shippingLines;

    /**
     * @var ShippingDto $shipping
     */
    private ShippingDto $shipping;

    /**
     * @var BillingDto $billing
     */
    private BillingDto $billing;

    /**
     * @var LockerDto|null $locker
     */
    private ?LockerDto $locker;

    /**
     * @var bool $hasOpenPackage
     */
    private bool $hasOpenPackage;

    /**
     * @var bool $hasLockerFirstMile
     */
    private bool $hasLockerFirstMile;

    /**
     * @var int $packageType
     */
    private int $packageType;

    /**
     * @var int $awbPayment
     */
    private int $awbPayment;

    /**
     * @var float $insuranceValue
     */
    private float $insuranceValue;

    /**
     * @var float $repayment
     */
    private float $repayment;

    /**
     * @var string|null $clientReference
     */
    private ?string $clientReference;

    /**
     * @var string|null $observation
     */
    private ?string $observation;

    /**
     * @var array<int|string, mixed> $packageDimensions
     */
    private array $packageDimensions;

    /**
     * @param int $orderId
     * @param int $serviceId
     * @param int $pickupPointId
     * @param array $shippingLines
     * @param ShippingDto $shipping
     * @param BillingDto $billing
     * @param LockerDto|null $locker
     * @param bool $hasOpenPackage
     * @param bool $hasLockerFirstMile
     * @param int $packageType
     * @param int $awbPayment
     * @param float $insuranceValue
     * @param float $repayment
     * @param string|null $clientReference
     * @param string|null $observation
     * @param array $packageDimensions
     */
    public function __construct(
        int $orderId,
        int $serviceId,
        int $pickupPointId,
        array $shippingLines,
        ShippingDto $shipping,
        BillingDto $billing,
        ?LockerDto $locker,
        bool $hasOpenPackage,
        bool $hasLockerFirstMile,
        int $packageType,
        int $awbPayment,
        float $insuranceValue,
        float $repayment,
        ?string $clientReference,
        ?string $observation,
        array $packageDimensions
    ) {
        $this->orderId = $orderId;
        $this->serviceId = $serviceId;
        $this->pickupPointId = $pickupPointId;
        $this->shippingLines = $shippingLines;
        $this->shipping = $shipping;
        $this->billing = $billing;
        $this->locker = $locker;
        $this->hasOpenPackage = $hasOpenPackage;
        $this->hasLockerFirstMile = $hasLockerFirstMile;
        $this->packageType = $packageType;
        $this->awbPayment = $awbPayment;
        $this->insuranceValue = $insuranceValue;
        $this->repayment = $repayment;
        $this->clientReference = $clientReference;
        $this->observation = $observation;
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
     * @return int
     */
    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    /**
     * @return int
     */
    public function getPickupPointId(): int
    {
        return $this->pickupPointId;
    }

    /**
     * @return array<int, mixed>
     */
    public function getShippingLines(): array
    {
        return $this->shippingLines;
    }

    /**
     * @return ShippingDto
     */
    public function getShipping(): ShippingDto
    {
        return $this->shipping;
    }

    /**
     * @return BillingDto
     */
    public function getBilling(): BillingDto
    {
        return $this->billing;
    }

    /**
     * @return LockerDto|null
     */
    public function getLocker(): ?LockerDto
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
     * @return bool
     */
    public function hasLockerFirstMile(): bool
    {
        return $this->hasLockerFirstMile;
    }

    /**
     * @return int
     */
    public function getPackageType(): int
    {
        return $this->packageType;
    }

    /**
     * @return int
     */
    public function getAwbPayment(): int
    {
        return $this->awbPayment;
    }

    /**
     * @return float
     */
    public function getInsuranceValue(): float
    {
        return $this->insuranceValue;
    }

    /**
     * @return float
     */
    public function getRepayment(): float
    {
        return $this->repayment;
    }

    /**
     * @return string|null
     */
    public function getClientReference(): ?string
    {
        return $this->clientReference;
    }

    /**
     * @return string|null
     */
    public function getObservation(): ?string
    {
        return $this->observation;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getPackageDimensions(): array
    {
        return $this->packageDimensions;
    }
}
