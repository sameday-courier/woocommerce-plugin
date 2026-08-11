<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbItem implements ItemInterface
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
     * @var mixed $insuranceValue
     */
    private $insuranceValue;

    /**
     * @var mixed $repayment
     */
    private $repayment;

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
     * @param array $shipping
     * @param array $billing
     * @param mixed $locker
     * @param bool $hasOpenPackage
     * @param bool $hasLockerFirstMile
     * @param int $packageType
     * @param int $awbPayment
     * @param mixed $insuranceValue
     * @param mixed $repayment
     * @param string|null $clientReference
     * @param string|null $observation
     * @param array $packageDimensions
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

    /**
     * @param array<string, mixed> $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        $clientReference = $inputParams['samedaycourier-client-reference'] ?? null;
        $observation = $inputParams['samedaycourier-package-observation'] ?? null;

        return new self(
            (int) ($inputParams['samedaycourier-order-id'] ?? 0),
            (int) ($inputParams['samedaycourier-service'] ?? 0),
            (int) ($inputParams['samedaycourier-package-pickup-point'] ?? 0),
            (array) ($inputParams['shipping_lines'] ?? []),
            (array) ($inputParams['shipping'] ?? []),
            (array) ($inputParams['billing'] ?? []),
            $inputParams['locker'] ?? null,
            isset($inputParams['samedaycourier-open-package-status']),
            isset($inputParams['samedaycourier-locker_first_mile']),
            (int) ($inputParams['samedaycourier-package-type'] ?? 0),
            (int) ($inputParams['samedaycourier-package-awb-payment'] ?? 0),
            $inputParams['samedaycourier-package-insurance-value'] ?? null,
            $inputParams['samedaycourier-package-repayment'] ?? null,
            null !== $clientReference ? (string) $clientReference : null,
            null !== $observation ? (string) $observation : null,
            (array) ($inputParams['samedaycourier-package-dimensions'] ?? []),
        );
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
