<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

use SamedayCourier\Shipping\Domain\DTOs\RecipientDto;

final class PostAwbRequestDto
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

    private ?int $cashOnDeliveryCollector;

    /**
     * @var mixed
     */
    private $thirdPartyPickup;

    /**
     * @var array<int, int|string>
     */
    private array $serviceTaxIds;

    private ?int $deliveryIntervalServiceType;

    private ?string $reference;

    private ?string $observation;

    private ?string $priceObservation;

    private ?string $clientObservation;

    private ?int $lockerFirstMile;

    private ?int $lockerLastMile;

    private ?int $oohFirstMile;

    private ?int $oohLastMile;

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
     * @param ?int $cashOnDeliveryCollector
     * @param mixed $thirdPartyPickup
     * @param array $serviceTaxIds
     * @param ?int $deliveryIntervalServiceType
     * @param ?string $reference
     * @param ?string $observation
     * @param ?string $priceObservation
     * @param ?string $clientObservation
     * @param ?int $lockerFirstMile
     * @param ?int $lockerLastMile
     * @param ?int $oohFirstMile
     * @param ?int $oohLastMile
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
        ?int $cashOnDeliveryCollector = null,
        $thirdPartyPickup = null,
        array $serviceTaxIds = [],
        ?int $deliveryIntervalServiceType = null,
        ?string $reference = null,
        ?string $observation = null,
        ?string $priceObservation = null,
        ?string $clientObservation = null,
        ?int $lockerFirstMile = null,
        ?int $lockerLastMile = null,
        ?int $oohFirstMile = null,
        ?int $oohLastMile = null,
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
        $this->cashOnDeliveryCollector = $cashOnDeliveryCollector;
        $this->thirdPartyPickup = $thirdPartyPickup;
        $this->serviceTaxIds = $serviceTaxIds;
        $this->deliveryIntervalServiceType = $deliveryIntervalServiceType;
        $this->reference = $reference;
        $this->observation = $observation;
        $this->priceObservation = $priceObservation;
        $this->clientObservation = $clientObservation;
        $this->lockerFirstMile = $lockerFirstMile;
        $this->lockerLastMile = $lockerLastMile;
        $this->oohFirstMile = $oohFirstMile;
        $this->oohLastMile = $oohLastMile;
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
     * @return array<int,
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
     * @return ?int
     */
    public function getCashOnDeliveryCollector(): ?int
    {
        return $this->cashOnDeliveryCollector;
    }

    /**
     * @return mixed
     */
    public function getThirdPartyPickup()
    {
        return $this->thirdPartyPickup;
    }

    /**
     * @return array<int,
     */
    public function getServiceTaxIds(): array
    {
        return $this->serviceTaxIds;
    }

    /**
     * @return ?int
     */
    public function getDeliveryIntervalServiceType(): ?int
    {
        return $this->deliveryIntervalServiceType;
    }

    /**
     * @return ?string
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @return ?string
     */
    public function getObservation(): ?string
    {
        return $this->observation;
    }

    /**
     * @return ?string
     */
    public function getPriceObservation(): ?string
    {
        return $this->priceObservation;
    }

    /**
     * @return ?string
     */
    public function getClientObservation(): ?string
    {
        return $this->clientObservation;
    }

    /**
     * @return ?int
     */
    public function getLockerFirstMile(): ?int
    {
        return $this->lockerFirstMile;
    }

    /**
     * @return ?int
     */
    public function getLockerLastMile(): ?int
    {
        return $this->lockerLastMile;
    }

    /**
     * @return ?int
     */
    public function getOohFirstMile(): ?int
    {
        return $this->oohFirstMile;
    }

    /**
     * @return ?int
     */
    public function getOohLastMile(): ?int
    {
        return $this->oohLastMile;
    }

    /**
     * @return ?string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }
}
