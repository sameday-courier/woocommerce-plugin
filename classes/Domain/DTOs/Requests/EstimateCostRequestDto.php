<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

use SamedayCourier\Shipping\Domain\DTOs\RecipientDto;

final class EstimateCostRequestDto
{
    private int $pickupPointId;

    private ?int $contactPersonId;

    private int $packageType;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $parcelsDimensions;

    private int $serviceId;

    private int $awbPayment;

    private RecipientDto $awbRecipient;

    private float $insuredValue;

    private float $cashOnDeliveryAmount;

    /**
     * @var mixed
     */
    private $thirdPartyPickup;

    /**
     * @var array<int, int|string>
     */
    private array $serviceTaxIds;

    private ?string $currency;

    /**
     * @param int $pickupPointId
     * @param ?int $contactPersonId
     * @param int $packageType
     * @param array $parcelsDimensions
     * @param int $serviceId
     * @param int $awbPayment
     * @param RecipientDto $awbRecipient
     * @param float $insuredValue
     * @param float $cashOnDeliveryAmount
     * @param mixed $thirdPartyPickup
     * @param array $serviceTaxIds
     * @param ?string $currency
     */
    public function __construct(
        int $pickupPointId,
        ?int $contactPersonId,
        int $packageType,
        array $parcelsDimensions,
        int $serviceId,
        int $awbPayment,
        RecipientDto $awbRecipient,
        float $insuredValue,
        float $cashOnDeliveryAmount = 0.0,
        $thirdPartyPickup = null,
        array $serviceTaxIds = [],
        ?string $currency = null
    ) {
        $this->pickupPointId = $pickupPointId;
        $this->contactPersonId = $contactPersonId;
        $this->packageType = $packageType;
        $this->parcelsDimensions = $parcelsDimensions;
        $this->serviceId = $serviceId;
        $this->awbPayment = $awbPayment;
        $this->awbRecipient = $awbRecipient;
        $this->insuredValue = $insuredValue;
        $this->cashOnDeliveryAmount = $cashOnDeliveryAmount;
        $this->thirdPartyPickup = $thirdPartyPickup;
        $this->serviceTaxIds = $serviceTaxIds;
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getPickupPointId(): int
    {
        return $this->pickupPointId;
    }

    /**
     * @return ?int
     */
    public function getContactPersonId(): ?int
    {
        return $this->contactPersonId;
    }

    /**
     * @return int
     */
    public function getPackageType(): int
    {
        return $this->packageType;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getParcelsDimensions(): array
    {
        return $this->parcelsDimensions;
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
    public function getAwbPayment(): int
    {
        return $this->awbPayment;
    }

    /**
     * @return RecipientDto
     */
    public function getAwbRecipient(): RecipientDto
    {
        return $this->awbRecipient;
    }

    /**
     * @return float
     */
    public function getInsuredValue(): float
    {
        return $this->insuredValue;
    }

    /**
     * @return float
     */
    public function getCashOnDeliveryAmount(): float
    {
        return $this->cashOnDeliveryAmount;
    }

    /**
     * @return mixed
     */
    public function getThirdPartyPickup()
    {
        return $this->thirdPartyPickup;
    }

    /**
     * @return array<int, int|string>
     */
    public function getServiceTaxIds(): array
    {
        return $this->serviceTaxIds;
    }

    /**
     * @return ?string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }
}
