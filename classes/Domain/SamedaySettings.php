<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

use Sameday\Objects\Types\AwbPdfType;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\UrlBuilder;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OptionsHandler;

final class SamedaySettings
{
    public const OPTION_KEY = 'woocommerce_samedaycourier_settings';
    public const ENABLED = 'enabled';
    public const TITLE = 'title';
    public const USER = 'user';
    public const PASSWORD = 'password';
    public const DEFAULT_LABEL_FORMAT = 'default_label_format';
    public const ESTIMATED_COST = 'estimated_cost';
    public const ESTIMATED_COST_EXTRA_FEE = 'estimated_cost_extra_fee';
    public const REPAYMENT_TAX_LABEL = 'repayment_tax_label';
    public const REPAYMENT_TAX = 'repayment_tax';
    public const OPEN_PACKAGE_STATUS = 'open_package_status';
    public const DISCOUNT_FREE_SHIPPING = 'discount_free_shipping';
    public const OPEN_PACKAGE_LABEL = 'open_package_label';
    public const LOCKER_MAX_ITEMS = 'locker_max_items';
    public const LOCKERS_MAP = 'lockers_map';
    public const IS_TESTING = 'is_testing';
    public const HOST_COUNTRY = 'host_country';
    public const USE_NOMENCLATOR = 'use_nomenclator';
    public const SAMEDAY_SYNC_LOCKERS_TS = 'sameday_sync_lockers_ts';

    /**
     * @return array
     */
    public static function getSamedayOptions(): array
    {
        $settings = OptionsHandler::getOption(self::OPTION_KEY);
        if (false === $settings) {
            return [];
        }

        return is_array($settings) ? $settings : [];
    }

    /**
     * @param array $options
     */
    public static function setSamedayOptions(array $options): void
    {
        OptionsHandler::setOption(self::OPTION_KEY, $options);
    }

    /**
     * @return string
     */
    public static function getPathToSettingsPage(): string
    {
        return UrlBuilder::build(
            'admin.php',
            [
                'page' => 'wc-settings',
                'tab' => 'shipping',
                'section' => 'samedaycourier',
            ]
        );
    }

    /**
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return self::get(self::ENABLED, 'yes') !== 'no';
    }

    /**
     * @return string
     */
    public static function getTitle(): string
    {
        $title = self::get(self::TITLE);

        return is_string($title) && $title !== '' ? $title : 'SamedayCourier';
    }

    /**
     * @return string|null
     */
    public static function getUser(): ?string
    {
        $user = self::get(self::USER);

        return is_string($user) && $user !== '' ? $user : null;
    }

    /**
     * @return string|null
     */
    public static function getPassword(): ?string
    {
        $password = self::get(self::PASSWORD);

        return is_string($password) && $password !== '' ? $password : null;
    }

    /**
     * @return string
     */
    public static function getDefaultLabelFormat(): string
    {
        $format = self::get(self::DEFAULT_LABEL_FORMAT);

        return is_string($format) && $format !== '' ? $format : SamedayAwbPdfTypes::getLabelKeys()[AwbPdfType::A4];
    }

    /**
     * @return string
     */
    public static function getEstimatedCost(): string
    {
        $estimatedCost = self::get(self::ESTIMATED_COST, 'no');

        return is_string($estimatedCost) ? $estimatedCost : 'no';
    }

    /**
     * @return int
     */
    public static function getEstimatedCostExtraFee(): int
    {
        return (int) self::get(self::ESTIMATED_COST_EXTRA_FEE, 0);
    }

    /**
     * @return string|null
     */
    public static function getRepaymentTaxLabel(): ?string
    {
        $label = self::get(self::REPAYMENT_TAX_LABEL);

        return is_string($label) && $label !== '' ? $label : null;
    }

    /**
     * @return int|null
     */
    public static function getRepaymentTax(): ?int
    {
        $repaymentTax = self::get(self::REPAYMENT_TAX);

        if (null === $repaymentTax || $repaymentTax === '') {
            return null;
        }

        return (int) $repaymentTax;
    }

    /**
     * @return bool
     */
    public static function isOpenPackageStatusEnabled(): bool
    {
        return self::get(self::OPEN_PACKAGE_STATUS) === 'yes';
    }

    public static function isDiscountFreeShippingEnabled(): bool
    {
        $discountFreeShipping = self::get(self::DISCOUNT_FREE_SHIPPING);

        return !(null === $discountFreeShipping || 'no' === $discountFreeShipping);
    }

    /**
     * @return string|null
     */
    public static function getOpenPackageLabel(): ?string
    {
        $label = self::get(self::OPEN_PACKAGE_LABEL);

        return is_string($label) && $label !== '' ? $label : null;
    }

    /**
     * @return int
     */
    public static function getLockerMaxItems(): int
    {
        $lockerMaxItems = self::get(self::LOCKER_MAX_ITEMS);

        return null !== $lockerMaxItems ? (int) $lockerMaxItems : SamedayConstants::DEFAULT_VALUE_LOCKER_MAX_ITEMS;
    }

    /**
     * @return bool
     */
    public static function isLockersMapEnabled(): bool
    {
        return self::get(self::LOCKERS_MAP, 'yes') === 'yes';
    }

    /**
     * @return bool
     */
    public static function isTesting(): bool
    {
        $isTesting = self::get(self::IS_TESTING);

        return $isTesting === 'yes' || $isTesting === '1' || (int) $isTesting === 1;
    }

    /**
     * @return int
     */
    public static function getTestingMode(): int
    {
        return self::isTesting() ? SamedayConstants::API_DEMO : SamedayConstants::API_PROD;
    }

    /**
     * @return string
     */
    public static function getHostCountry(): string
    {
        $hostCountry = self::get(self::HOST_COUNTRY);

        return is_string($hostCountry) && $hostCountry !== '' && $hostCountry !== 'none'
            ? $hostCountry
            : SamedayConstants::API_HOST_LOCALE_RO;
    }

    /**
     * @return bool
     */
    public static function isUseSamedayNomenclator(): bool
    {
        $useSamedayNomenclator = self::get(self::USE_NOMENCLATOR);

        return ! (null === $useSamedayNomenclator || 'no' === $useSamedayNomenclator);
    }

    public static function getSamedaySyncLockersTs(): int
    {
        return (int) self::get(self::SAMEDAY_SYNC_LOCKERS_TS, 0);
    }

    public static function setSamedaySyncLockersTs(int $timestamp): void
    {
        $options = self::getSamedayOptions();
        $options[self::SAMEDAY_SYNC_LOCKERS_TS] = $timestamp;

        self::setSamedayOptions($options);
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    private static function get(string $key, $default = null)
    {
        return self::getSamedayOptions()[$key] ?? $default;
    }
}
