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
     * @param array<int, array<string, mixed>> $parcelsDimensions
     * @param array<int, int|string> $serviceTaxIds
     * @param mixed $thirdPartyPickup
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

    public function getPickupPointId(): int
    {
        return $this->pickupPointId;
    }

    public function getContactPersonId(): ?int
    {
        return $this->contactPersonId;
    }

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

    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    public function getAwbPayment(): int
    {
        return $this->awbPayment;
    }

    public function getAwbRecipient(): RecipientDto
    {
        return $this->awbRecipient;
    }

    public function getInsuredValue(): float
    {
        return $this->insuredValue;
    }

    public function getCashOnDeliveryAmount(): float
    {
        return $this->cashOnDeliveryAmount;
    }

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
     * @return array<int, int|string>
     */
    public function getServiceTaxIds(): array
    {
        return $this->serviceTaxIds;
    }

    public function getDeliveryIntervalServiceType(): ?int
    {
        return $this->deliveryIntervalServiceType;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function getPriceObservation(): ?string
    {
        return $this->priceObservation;
    }

    public function getClientObservation(): ?string
    {
        return $this->clientObservation;
    }

    public function getLockerFirstMile(): ?int
    {
        return $this->lockerFirstMile;
    }

    public function getLockerLastMile(): ?int
    {
        return $this->lockerLastMile;
    }

    public function getOohFirstMile(): ?int
    {
        return $this->oohFirstMile;
    }

    public function getOohLastMile(): ?int
    {
        return $this->oohLastMile;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }
}
