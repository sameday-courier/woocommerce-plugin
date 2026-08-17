<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class GenerateAwbServiceRequestDto
{
    private int $orderId;

    private int $serviceId;

    private int $pickupPointId;

    /**
     * @var array<int, mixed> $shippingLines
     */
    private array $shippingLines;

    /**
     * @var array<string, mixed> $shipping
     */
    private array $shipping;

    /**
     * @var array<string, mixed> $billing
     */
    private array $billing;

    /**
     * @var mixed $locker
     */
    private $locker;

    private bool $hasOpenPackage;

    private bool $hasLockerFirstMile;

    private int $packageType;

    private int $awbPayment;

    /**
     * @var mixed $insuranceValue
     */
    private $insuranceValue;

    /**
     * @var mixed $repayment
     */
    private $repayment;

    private ?string $clientReference;

    private ?string $observation;

    /**
     * @var array<int|string, mixed> $packageDimensions
     */
    private array $packageDimensions;

    /**
     * @param array<int, mixed> $shippingLines
     * @param array<string, mixed> $shipping
     * @param array<string, mixed> $billing
     * @param mixed $locker
     * @param mixed $insuranceValue
     * @param mixed $repayment
     * @param array<int|string, mixed> $packageDimensions
     */
    public function __construct(
        int $orderId,
        int $serviceId,
        int $pickupPointId,
        array $shippingLines,
        array $shipping,
        array $billing,
        $locker,
        bool $hasOpenPackage,
        bool $hasLockerFirstMile,
        int $packageType,
        int $awbPayment,
        $insuranceValue,
        $repayment,
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

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getServiceId(): int
    {
        return $this->serviceId;
    }

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
     * @return mixed
     */
    public function getLocker()
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

    public function getAwbPayment(): int
    {
        return $this->awbPayment;
    }

    /**
     * @return mixed
     */
    public function getInsuranceValue()
    {
        return $this->insuranceValue;
    }

    /**
     * @return mixed
     */
    public function getRepayment()
    {
        return $this->repayment;
    }

    public function getClientReference(): ?string
    {
        return $this->clientReference;
    }

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
