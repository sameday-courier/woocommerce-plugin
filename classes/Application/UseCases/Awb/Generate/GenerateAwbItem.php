<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\ThirdPartyPickupEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\CodCollectorType;
use Sameday\Objects\Types\DeliveryIntervalServiceType;
use Sameday\Objects\Types\PackageType;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbItem
{
    /**
     * @var int
     */
    private int $pickupPointId;

    /**
     * @var int|null
     */
    private ?int $contactPersonId;

    /**
     * @var PackageType
     */
    private PackageType $packageType;

    /**
     * @var ParcelDimensionsObject[]
     */
    private array $parcelsDimensions;

    /**
     * @var int
     */
    private int $serviceId;

    /**
     * @var AwbPaymentType
     */
    private AwbPaymentType $awbPayment;

    /**
     * @var AwbRecipientEntityObject
     */
    private AwbRecipientEntityObject $awbRecipient;

    /**
     * @var float
     */
    private float $insuredValue;

    /**
     * @var float
     */
    private float $cashOnDeliveryAmount;

    /**
     * @var CodCollectorType|null
     */
    private ?CodCollectorType $cashOnDeliveryCollector;

    /**
     * @var ThirdPartyPickupEntityObject|null
     */
    private ?ThirdPartyPickupEntityObject $thirdPartyPickup;

    /**
     * @var int[]
     */
    private array $serviceTaxIds;

    /**
     * @var DeliveryIntervalServiceType|null
     */
    private ?DeliveryIntervalServiceType $deliveryIntervalServiceType;

    /**
     * @var string|null
     */
    private ?string $reference;

    /**
     * @var string|null
     */
    private ?string $observation;

    /**
     * @var string|null
     */
    private ?string $priceObservation;

    /**
     * @var string|null
     */
    private ?string $clientObservation;

    /**
     * @var int|null
     */
    private ?int $lockerFirstMile;

    /**
     * @var int|null
     */
    private ?int $lockerLastMile;

    /**
     * @var int|null
     */
    private ?int $oohFirstMile;

    /**
     * @var int|null
     */
    private ?int $oohLastMile;

    /**
     * @var string|null
     */
    private ?string $currency;

    /**
     * @param int $pickupPointId
     * @param int|null $contactPersonId
     * @param PackageType $packageType
     * @param ParcelDimensionsObject[] $parcelsDimensions
     * @param int $serviceId
     * @param AwbPaymentType $awbPayment
     * @param AwbRecipientEntityObject $awbRecipient
     * @param float $insuredValue
     * @param float $cashOnDeliveryAmount
     * @param CodCollectorType|null $cashOnDeliveryCollector
     * @param ThirdPartyPickupEntityObject|null $thirdPartyPickup
     * @param int[] $serviceTaxIds
     * @param DeliveryIntervalServiceType|null $deliveryIntervalServiceType
     * @param string|null $reference
     * @param string|null $observation
     * @param string|null $priceObservation
     * @param string|null $clientObservation
     * @param int|null $lockerFirstMile
     * @param int|null $lockerLastMile
     * @param int|null $oohFirstMile
     * @param int|null $oohLastMile
     * @param string|null $currency
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

    /**
     * @return int
     */
    public function getPickupPointId(): int
    {
        return $this->pickupPointId;
    }

    /**
     * @param int $pickupPointId
     *
     * @return $this
     */
    public function setPickupPointId(int $pickupPointId): self
    {
        $this->pickupPointId = $pickupPointId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getContactPersonId(): ?int
    {
        return $this->contactPersonId;
    }

    /**
     * @param int|null $contactPersonId
     *
     * @return $this
     */
    public function setContactPersonId(?int $contactPersonId): self
    {
        $this->contactPersonId = $contactPersonId;

        return $this;
    }

    /**
     * @return PackageType
     */
    public function getPackageType(): PackageType
    {
        return $this->packageType;
    }

    /**
     * @param PackageType $packageType
     *
     * @return $this
     */
    public function setPackageType(PackageType $packageType): self
    {
        $this->packageType = $packageType;

        return $this;
    }

    /**
     * @return ParcelDimensionsObject[]
     */
    public function getParcelsDimensions(): array
    {
        return $this->parcelsDimensions;
    }

    /**
     * @param ParcelDimensionsObject[] $parcelsDimensions
     *
     * @return $this
     */
    public function setParcelsDimensions(array $parcelsDimensions): self
    {
        $this->parcelsDimensions = $parcelsDimensions;

        return $this;
    }

    /**
     * @return int
     */
    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    /**
     * @param int $serviceId
     *
     * @return $this
     */
    public function setServiceId(int $serviceId): self
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * @return AwbPaymentType
     */
    public function getAwbPayment(): AwbPaymentType
    {
        return $this->awbPayment;
    }

    /**
     * @param AwbPaymentType $awbPayment
     *
     * @return $this
     */
    public function setAwbPayment(AwbPaymentType $awbPayment): self
    {
        $this->awbPayment = $awbPayment;

        return $this;
    }

    /**
     * @return AwbRecipientEntityObject
     */
    public function getAwbRecipient(): AwbRecipientEntityObject
    {
        return $this->awbRecipient;
    }

    /**
     * @param AwbRecipientEntityObject $awbRecipient
     *
     * @return $this
     */
    public function setAwbRecipient(AwbRecipientEntityObject $awbRecipient): self
    {
        $this->awbRecipient = $awbRecipient;

        return $this;
    }

    /**
     * @return float
     */
    public function getInsuredValue(): float
    {
        return $this->insuredValue;
    }

    /**
     * @param float $insuredValue
     *
     * @return $this
     */
    public function setInsuredValue(float $insuredValue): self
    {
        $this->insuredValue = $insuredValue;

        return $this;
    }

    /**
     * @return float
     */
    public function getCashOnDeliveryAmount(): float
    {
        return $this->cashOnDeliveryAmount;
    }

    /**
     * @param float $cashOnDeliveryAmount
     *
     * @return $this
     */
    public function setCashOnDeliveryAmount(float $cashOnDeliveryAmount): self
    {
        $this->cashOnDeliveryAmount = $cashOnDeliveryAmount;

        return $this;
    }

    /**
     * @return CodCollectorType|null
     */
    public function getCashOnDeliveryCollector(): ?CodCollectorType
    {
        return $this->cashOnDeliveryCollector;
    }

    /**
     * @param CodCollectorType|null $cashOnDeliveryCollector
     *
     * @return $this
     */
    public function setCashOnDeliveryCollector(?CodCollectorType $cashOnDeliveryCollector): self
    {
        $this->cashOnDeliveryCollector = $cashOnDeliveryCollector;

        return $this;
    }

    /**
     * @return ThirdPartyPickupEntityObject|null
     */
    public function getThirdPartyPickup(): ?ThirdPartyPickupEntityObject
    {
        return $this->thirdPartyPickup;
    }

    /**
     * @param ThirdPartyPickupEntityObject|null $thirdPartyPickup
     *
     * @return $this
     */
    public function setThirdPartyPickup(?ThirdPartyPickupEntityObject $thirdPartyPickup): self
    {
        $this->thirdPartyPickup = $thirdPartyPickup;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getServiceTaxIds(): array
    {
        return $this->serviceTaxIds;
    }

    /**
     * @param int[] $serviceTaxIds
     *
     * @return $this
     */
    public function setServiceTaxIds(array $serviceTaxIds): self
    {
        $this->serviceTaxIds = $serviceTaxIds;

        return $this;
    }

    /**
     * @return DeliveryIntervalServiceType|null
     */
    public function getDeliveryIntervalServiceType(): ?DeliveryIntervalServiceType
    {
        return $this->deliveryIntervalServiceType;
    }

    /**
     * @param DeliveryIntervalServiceType|null $deliveryIntervalServiceType
     *
     * @return $this
     */
    public function setDeliveryIntervalServiceType(?DeliveryIntervalServiceType $deliveryIntervalServiceType): self
    {
        $this->deliveryIntervalServiceType = $deliveryIntervalServiceType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param string|null $reference
     *
     * @return $this
     */
    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getObservation(): ?string
    {
        return $this->observation;
    }

    /**
     * @param string|null $observation
     *
     * @return $this
     */
    public function setObservation(?string $observation): self
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPriceObservation(): ?string
    {
        return $this->priceObservation;
    }

    /**
     * @param string|null $priceObservation
     *
     * @return $this
     */
    public function setPriceObservation(?string $priceObservation): self
    {
        $this->priceObservation = $priceObservation;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientObservation(): ?string
    {
        return $this->clientObservation;
    }

    /**
     * @param string|null $clientObservation
     *
     * @return $this
     */
    public function setClientObservation(?string $clientObservation): self
    {
        $this->clientObservation = $clientObservation;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLockerFirstMile(): ?int
    {
        return $this->lockerFirstMile;
    }

    /**
     * @param int|null $lockerFirstMile
     *
     * @return $this
     */
    public function setLockerFirstMile(?int $lockerFirstMile): self
    {
        $this->lockerFirstMile = $lockerFirstMile;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLockerLastMile(): ?int
    {
        return $this->lockerLastMile;
    }

    /**
     * @param int|null $lockerLastMile
     *
     * @return $this
     */
    public function setLockerLastMile(?int $lockerLastMile): self
    {
        $this->lockerLastMile = $lockerLastMile;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOohFirstMile(): ?int
    {
        return $this->oohFirstMile;
    }

    /**
     * @param int|null $oohFirstMile
     *
     * @return $this
     */
    public function setOohFirstMile(?int $oohFirstMile): self
    {
        $this->oohFirstMile = $oohFirstMile;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOohLastMile(): ?int
    {
        return $this->oohLastMile;
    }

    /**
     * @param int|null $oohLastMile
     *
     * @return $this
     */
    public function setOohLastMile(?int $oohLastMile): self
    {
        $this->oohLastMile = $oohLastMile;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string|null $currency
     *
     * @return $this
     */
    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}
