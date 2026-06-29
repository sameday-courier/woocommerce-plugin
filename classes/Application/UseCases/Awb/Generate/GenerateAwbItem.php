<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use JsonException;
use Sameday\Objects\ParcelDimensionsObject;
use SamedayCourier\Shipping\Domain\DTOs\BillingDto;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\DTOs\ShippingDto;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbItem
{
    private int $orderId;

    private int $serviceId;

    /**
     * @var array<int, mixed>
     */
    private array $shippingLines;

    private ShippingDto $shipping;

    private BillingDto $billing;

    /**
     * @var LockerDto|null
     */
    private LockerDto $locker;

    private bool $hasOpenPackage;

    private bool $hasLockerFirstMile;

    private int $packageType;

    private int $pickupPointId;

    private int $awbPayment;

    /**
     * @var float|string
     */
    private $insuranceValue;

    /**
     * @var float|string
     */
    private $repayment;

    private ?string $clientReference;

    private ?string $observation;

    /**
     * @var ParcelDimensionsObject[]
     */
    private array $parcelsDimensions;

    /**
     * @param int $orderId
     * @param int $serviceId
     * @param array<int, mixed> $shippingLines
     * @param ShippingDto $shipping
     * @param BillingDto $billing
     * @param LockerDto $locker
     * @param bool $hasOpenPackage
     * @param bool $hasLockerFirstMile
     * @param int $packageType
     * @param int $pickupPointId
     * @param int $awbPayment
     * @param float|string $insuranceValue
     * @param float|string $repayment
     * @param string|null $clientReference
     * @param string|null $observation
     * @param ParcelDimensionsObject[] $parcelsDimensions
     */
    public function __construct(
        int $orderId,
        int $serviceId,
        array $shippingLines,
        ShippingDto $shipping,
        BillingDto $billing,
        LockerDto $locker,
        bool $hasOpenPackage,
        bool $hasLockerFirstMile,
        int $packageType,
        int $pickupPointId,
        int $awbPayment,
        $insuranceValue,
        $repayment,
        ?string $clientReference,
        ?string $observation,
        array $parcelsDimensions
    ) {
        $this->orderId = $orderId;
        $this->serviceId = $serviceId;
        $this->shippingLines = $shippingLines;
        $this->shipping = $shipping;
        $this->billing = $billing;
        $this->locker = $locker;
        $this->hasOpenPackage = $hasOpenPackage;
        $this->hasLockerFirstMile = $hasLockerFirstMile;
        $this->packageType = $packageType;
        $this->pickupPointId = $pickupPointId;
        $this->awbPayment = $awbPayment;
        $this->insuranceValue = $insuranceValue;
        $this->repayment = $repayment;
        $this->clientReference = $clientReference;
        $this->observation = $observation;
        $this->parcelsDimensions = $parcelsDimensions;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $parcelDimensions = [];

        foreach ($data as $key => $value) {
            if (!preg_match('/^samedaycourier-package-(weight|length|height|width)(\d+)$/', $key, $matches)) {
                continue;
            }

            $attribute = $matches[1];
            $index = $matches[2];

            if (!isset($parcelDimensions[$index])) {
                $parcelDimensions[$index] = [
                    'weight' => null,
                    'length' => null,
                    'height' => null,
                    'width' => null,
                ];
            }

            $parcelDimensions[$index][$attribute] = $value;
        }

        $parcelsDimensions = [];
        foreach ($parcelDimensions as $dimension) {
            $parcelsDimensions[] = new ParcelDimensionsObject(
                $dimension['weight'],
                $dimension['length'],
                $dimension['height'],
                $dimension['width']
            );
        }

        if ('' !== $lockerData = $data['locker']) {
            try {
                $lockerData = json_decode($lockerData, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $lockerData = [];
            }
        }


        return new self(
            (int) $data['samedaycourier-order-id'],
            (int) $data['samedaycourier-service'],
            (array) ($data['shipping_lines'] ?? []),
            ShippingDto::fromArray((array) ($data['shipping'] ?? [])),
            BillingDto::fromArray((array) ($data['billing'] ?? [])),
            LockerDto::fromArray((array) $lockerData),
            isset($data['samedaycourier-open-package-status']),
            isset($data['samedaycourier-locker_first_mile']),
            (int) $data['samedaycourier-package-type'],
            (int) $data['samedaycourier-package-pickup-point'],
            (int) $data['samedaycourier-package-awb-payment'],
            $data['samedaycourier-package-insurance-value'],
            $data['samedaycourier-package-repayment'],
            $data['samedaycourier-client-reference'] ?? null,
            $data['samedaycourier-package-observation'] ?? null,
            $parcelsDimensions,
        );
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getServiceId(): int
    {
        return $this->serviceId;
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

    public function getPickupPointId(): int
    {
        return $this->pickupPointId;
    }

    public function getAwbPayment(): int
    {
        return $this->awbPayment;
    }

    /**
     * @return float|string
     */
    public function getInsuranceValue()
    {
        return $this->insuranceValue;
    }

    /**
     * @return float|string
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
     * @return ParcelDimensionsObject[]
     */
    public function getParcelsDimensions(): array
    {
        return $this->parcelsDimensions;
    }
}
