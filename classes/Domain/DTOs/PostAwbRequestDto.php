<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\ThirdPartyPickupEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\DeliveryIntervalServiceType;
use Sameday\Objects\Types\PackageType;

final class PostAwbRequestDto
{
    private int $pickupPointId;

    private ?int $contactPersonId;

    private PackageType $packageType;

    /**
     * @var ParcelDimensionsObject[]
     */
    private array $parcelsDimensions;

    private int $serviceId;

    private AwbPaymentType $awbPayment;

    private AwbRecipientEntityObject $awbRecipient;

    private float $insuredValue;

    private float $cashOnDeliveryAmount;

    private ?CodCollectorType $cashOnDeliveryCollector;

    private ?ThirdPartyPickupEntityObject $thirdPartyPickup;

    /**
     * @var int[]
     */
    private array $serviceTaxIds;

    private ?DeliveryIntervalServiceType $deliveryIntervalServiceType;

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
     * @param ParcelDimensionsObject[] $parcelsDimensions
     * @param int[] $serviceTaxIds
     */
    public function __construct(
        int $pickupPointId,
        ?int $contactPersonId,
        PackageType $packageType,
        array $parcelsDimensions,
        int $serviceId,
        AwbPaymentType $awbPayment,
        AwbRecipientEntityObject $awbRecipient,
        float $insuredValue,
        float $cashOnDeliveryAmount = 0.0,
        ?CodCollectorType $cashOnDeliveryCollector = null,
        ?ThirdPartyPickupEntityObject $thirdPartyPickup = null,
        array $serviceTaxIds = [],
        ?DeliveryIntervalServiceType $deliveryIntervalServiceType = null,
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

    public function getPackageType(): PackageType
    {
        return $this->packageType;
    }

    /**
     * @return ParcelDimensionsObject[]
     */
    public function getParcelsDimensions(): array
    {
        return $this->parcelsDimensions;
    }

    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    public function getAwbPayment(): AwbPaymentType
    {
        return $this->awbPayment;
    }

    public function getAwbRecipient(): AwbRecipientEntityObject
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

    public function getCashOnDeliveryCollector(): ?CodCollectorType
    {
        return $this->cashOnDeliveryCollector;
    }

    public function getThirdPartyPickup(): ?ThirdPartyPickupEntityObject
    {
        return $this->thirdPartyPickup;
    }

    /**
     * @return int[]
     */
    public function getServiceTaxIds(): array
    {
        return $this->serviceTaxIds;
    }

    public function getDeliveryIntervalServiceType(): ?DeliveryIntervalServiceType
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
