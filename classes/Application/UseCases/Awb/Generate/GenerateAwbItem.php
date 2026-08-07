<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Sameday\Objects\ParcelDimensionsObject;
use SamedayCourier\Shipping\Application\Common\Services\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\DTOs\BillingDto;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\DTOs\ShippingDto;
use SamedayCourier\Shipping\Domain\Models\SamedayPickupPoint;
use SamedayCourier\Shipping\Domain\Models\SamedayService;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbItem
{
    private int $orderId;

    /**
     * @var SamedayService $service
     */
    private SamedayService $service;

    /**
     * @var SamedayPickupPoint $pickupPoint
     */
    private SamedayPickupPoint $pickupPoint;

    /**
     * @var array<int, mixed>
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
     * @var float|string
     */
    private $insuranceValue;

    /**
     * @var float|string
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
     * @var ParcelDimensionsObject[]
     */
    private array $parcelsDimensions;

    /**
     * @param int $orderId
     * @param SamedayService $service
     * @param SamedayPickupPoint $pickupPoint
     * @param array $shippingLines
     * @param ShippingDto $shipping
     * @param BillingDto $billing
     * @param LockerDto|null $locker
     * @param bool $hasOpenPackage
     * @param bool $hasLockerFirstMile
     * @param int $packageType
     * @param int $awbPayment
     * @param $insuranceValue
     * @param $repayment
     * @param string|null $clientReference
     * @param string|null $observation
     * @param array $parcelsDimensions
     */
    public function __construct(
        int $orderId,
        SamedayService $service,
        SamedayPickupPoint $pickupPoint,
        array $shippingLines,
        ShippingDto $shipping,
        BillingDto $billing,
        ?LockerDto $locker,
        bool $hasOpenPackage,
        bool $hasLockerFirstMile,
        int $packageType,
        int $awbPayment,
        $insuranceValue,
        $repayment,
        ?string $clientReference,
        ?string $observation,
        array $parcelsDimensions
    ) {
        $this->orderId = $orderId;
        $this->service = $service;
        $this->pickupPoint = $pickupPoint;
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

        $serviceRepository = new SamedayServiceRepository();
        $pickupPointRepository = new SamedayPickupPointRepository();
        $samedayService = $serviceRepository->getServiceSameday(
            (int) $data['samedaycourier-service']
        );
        $samedayPickupPoint = $pickupPointRepository->getPickupPointSameday(
            (int) $data['samedaycourier-package-pickup-point']
        );

        return new self(
            (int) $data['samedaycourier-order-id'],
            $samedayService,
            $samedayPickupPoint,
            (array) ($data['shipping_lines'] ?? []),
            ShippingDto::fromArray((array) ($data['shipping'] ?? [])),
            BillingDto::fromArray((array) ($data['billing'] ?? [])),
            (new LockerDtoFactory())->fromInput($data['locker'] ?? null),
            isset($data['samedaycourier-open-package-status']),
            isset($data['samedaycourier-locker_first_mile']),
            (int) $data['samedaycourier-package-type'],
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

    /**
     * @return SamedayService
     */
    public function getService(): SamedayService
    {
        return $this->service;
    }

    /**
     * @return SamedayPickupPoint
     */
    public function getPickupPoint(): SamedayPickupPoint
    {
        return $this->pickupPoint;
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
