<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

final class CarrierSettings
{
    /**
     * @var bool
     */
    private bool $enabled;

    /**
     * @var string
     */
    private string $title;

    /**
     * @var string|null
     */
    private ?string $user;

    /**
     * @var string|null
     */
    private ?string $password;

    /**
     * @var string
     */
    private string $defaultLabelFormat;

    /**
     * @var string
     */
    private string $estimatedCost;

    /**
     * @var int
     */
    private int $estimatedCostExtraFee;

    /**
     * @var string|null
     */
    private ?string $repaymentTaxLabel;

    /**
     * @var int|null
     */
    private ?int $repaymentTax;

    /**
     * @var bool
     */
    private bool $openPackageStatusEnabled;

    /**
     * @var bool
     */
    private bool $discountFreeShippingEnabled;

    /**
     * @var string|null
     */
    private ?string $openPackageLabel;

    /**
     * @var int
     */
    private int $lockerMaxItems;

    /**
     * @var bool
     */
    private bool $lockersMapEnabled;

    /**
     * @var bool
     */
    private bool $testing;

    /**
     * @var string
     */
    private string $hostCountry;

    /**
     * @var bool
     */
    private bool $useSamedayNomenclator;

    /**
     * @var int
     */
    private int $samedaySyncLockersTs;

    /**
     * @param bool $enabled
     * @param string $title
     * @param string|null $user
     * @param string|null $password
     * @param string $defaultLabelFormat
     * @param string $estimatedCost
     * @param int $estimatedCostExtraFee
     * @param string|null $repaymentTaxLabel
     * @param int|null $repaymentTax
     * @param bool $openPackageStatusEnabled
     * @param bool $discountFreeShippingEnabled
     * @param string|null $openPackageLabel
     * @param int $lockerMaxItems
     * @param bool $lockersMapEnabled
     * @param bool $testing
     * @param string $hostCountry
     * @param bool $useSamedayNomenclator
     * @param int $samedaySyncLockersTs
     */
    public function __construct(
        bool    $enabled,
        string  $title,
        ?string $user,
        ?string $password,
        string  $defaultLabelFormat,
        string  $estimatedCost,
        int     $estimatedCostExtraFee,
        ?string $repaymentTaxLabel,
        ?int    $repaymentTax,
        bool    $openPackageStatusEnabled,
        bool    $discountFreeShippingEnabled,
        ?string $openPackageLabel,
        int     $lockerMaxItems,
        bool    $lockersMapEnabled,
        bool    $testing,
        string  $hostCountry,
        bool    $useSamedayNomenclator,
        int     $samedaySyncLockersTs
    )
    {
        $this->enabled = $enabled;
        $this->title = $title;
        $this->user = $user;
        $this->password = $password;
        $this->defaultLabelFormat = $defaultLabelFormat;
        $this->estimatedCost = $estimatedCost;
        $this->estimatedCostExtraFee = $estimatedCostExtraFee;
        $this->repaymentTaxLabel = $repaymentTaxLabel;
        $this->repaymentTax = $repaymentTax;
        $this->openPackageStatusEnabled = $openPackageStatusEnabled;
        $this->discountFreeShippingEnabled = $discountFreeShippingEnabled;
        $this->openPackageLabel = $openPackageLabel;
        $this->lockerMaxItems = $lockerMaxItems;
        $this->lockersMapEnabled = $lockersMapEnabled;
        $this->testing = $testing;
        $this->hostCountry = $hostCountry;
        $this->useSamedayNomenclator = $useSamedayNomenclator;
        $this->samedaySyncLockersTs = $samedaySyncLockersTs;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getDefaultLabelFormat(): string
    {
        return $this->defaultLabelFormat;
    }

    /**
     * @return string
     */
    public function getEstimatedCost(): string
    {
        return $this->estimatedCost;
    }

    /**
     * @return int
     */
    public function getEstimatedCostExtraFee(): int
    {
        return $this->estimatedCostExtraFee;
    }

    /**
     * @return string|null
     */
    public function getRepaymentTaxLabel(): ?string
    {
        return $this->repaymentTaxLabel;
    }

    /**
     * @return int|null
     */
    public function getRepaymentTax(): ?int
    {
        return $this->repaymentTax;
    }

    /**
     * @return bool
     */
    public function isOpenPackageStatusEnabled(): bool
    {
        return $this->openPackageStatusEnabled;
    }

    /**
     * @return bool
     */
    public function isDiscountFreeShippingEnabled(): bool
    {
        return $this->discountFreeShippingEnabled;
    }

    /**
     * @return string|null
     */
    public function getOpenPackageLabel(): ?string
    {
        return $this->openPackageLabel;
    }

    /**
     * @return int
     */
    public function getLockerMaxItems(): int
    {
        return $this->lockerMaxItems;
    }

    /**
     * @return bool
     */
    public function isLockersMapEnabled(): bool
    {
        return $this->lockersMapEnabled;
    }

    /**
     * @return bool
     */
    public function isTesting(): bool
    {
        return $this->testing;
    }

    /**
     * @return int
     */
    public function getTestingMode(): int
    {
        return $this->testing ? CarrierConstants::API_DEMO : CarrierConstants::API_PROD;
    }

    /**
     * @return string
     */
    public function getHostCountry(): string
    {
        return $this->hostCountry;
    }

    /**
     * @return bool
     */
    public function isUseSamedayNomenclator(): bool
    {
        return $this->useSamedayNomenclator;
    }

    /**
     * @return int
     */
    public function getSamedaySyncLockersTs(): int
    {
        return $this->samedaySyncLockersTs;
    }
}
