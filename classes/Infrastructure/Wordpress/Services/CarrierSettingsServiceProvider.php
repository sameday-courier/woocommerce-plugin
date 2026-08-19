<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\Types\AwbPdfType;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\CarrierAwbPdfTypes;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierSettings;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\OptionsHandler;

final class CarrierSettingsServiceProvider implements CarrierSettingsProviderInterface
{
    private const OPTION_KEY = 'woocommerce_samedaycourier_settings';
    private const ENABLED = 'enabled';
    private const TITLE = 'title';
    private const USER = 'user';
    private const PASSWORD = 'password';
    private const DEFAULT_LABEL_FORMAT = 'default_label_format';
    private const ESTIMATED_COST = 'estimated_cost';
    private const ESTIMATED_COST_EXTRA_FEE = 'estimated_cost_extra_fee';
    private const REPAYMENT_TAX_LABEL = 'repayment_tax_label';
    private const REPAYMENT_TAX = 'repayment_tax';
    private const OPEN_PACKAGE_STATUS = 'open_package_status';
    private const DISCOUNT_FREE_SHIPPING = 'discount_free_shipping';
    private const OPEN_PACKAGE_LABEL = 'open_package_label';
    private const LOCKER_MAX_ITEMS = 'locker_max_items';
    private const LOCKERS_MAP = 'lockers_map';
    private const IS_TESTING = 'is_testing';
    private const HOST_COUNTRY = 'host_country';
    private const USE_NOMENCLATOR = 'use_nomenclator';
    private const SAMEDAY_SYNC_LOCKERS_TS = 'sameday_sync_lockers_ts';

    /**
     * @return CarrierSettings
     */
    public function get(): CarrierSettings
    {
        $options = $this->getSamedayOptions();

        return new CarrierSettings(
            $this->resolveEnabled($options),
            $this->resolveTitle($options),
            $this->resolveNullableString($options, self::USER),
            $this->resolveNullableString($options, self::PASSWORD),
            $this->resolveDefaultLabelFormat($options),
            $this->resolveEstimatedCost($options),
            (int)($options[self::ESTIMATED_COST_EXTRA_FEE] ?? 0),
            $this->resolveNullableString($options, self::REPAYMENT_TAX_LABEL),
            $this->resolveRepaymentTax($options),
            ($options[self::OPEN_PACKAGE_STATUS] ?? null) === 'yes',
            $this->resolveEnabledUnlessNo($options, self::DISCOUNT_FREE_SHIPPING),
            $this->resolveNullableString($options, self::OPEN_PACKAGE_LABEL),
            $this->resolveLockerMaxItems($options),
            ($options[self::LOCKERS_MAP] ?? 'yes') === 'yes',
            $this->resolveIsTesting($options),
            $this->resolveHostCountry($options),
            $this->resolveEnabledUnlessNo($options, self::USE_NOMENCLATOR),
            (int) ($options[self::SAMEDAY_SYNC_LOCKERS_TS] ?? 0)
        );
    }

    /**
     * @param int $timestamp
     *
     * @return void
     */
    public function setSamedaySyncLockersTs(int $timestamp): void
    {
        $options = $this->getSamedayOptions();
        $options[self::SAMEDAY_SYNC_LOCKERS_TS] = $timestamp;

        OptionsHandler::setOption(self::OPTION_KEY, $options);
    }

    /**
     * @return array<string,
     */
    private function getSamedayOptions(): array
    {
        $settings = OptionsHandler::getOption(self::OPTION_KEY);
        if (false === $settings || !is_array($settings)) {
            return [];
        }

        return $settings;
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    private function resolveEnabled(array $options): bool
    {
        return ($options[self::ENABLED] ?? 'yes') !== 'no';
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function resolveTitle(array $options): string
    {
        $title = $options[self::TITLE] ?? null;

        return is_string($title) && $title !== '' ? $title : 'SamedayCourier';
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function resolveDefaultLabelFormat(array $options): string
    {
        $format = $options[self::DEFAULT_LABEL_FORMAT] ?? null;

        return is_string($format) && $format !== ''
            ? $format
            : CarrierAwbPdfTypes::getLabelKeys()[AwbPdfType::A4];
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function resolveEstimatedCost(array $options): string
    {
        $estimatedCost = $options[self::ESTIMATED_COST] ?? 'no';

        return is_string($estimatedCost) ? $estimatedCost : 'no';
    }

    /**
     * @param array $options
     *
     * @return int|null
     */
    private function resolveRepaymentTax(array $options): ?int
    {
        $repaymentTax = $options[self::REPAYMENT_TAX] ?? null;

        if (null === $repaymentTax || $repaymentTax === '') {
            return null;
        }

        return (int)$repaymentTax;
    }

    /**
     * @param array $options
     *
     * @return int
     */
    private function resolveLockerMaxItems(array $options): int
    {
        $lockerMaxItems = $options[self::LOCKER_MAX_ITEMS] ?? null;

        return null !== $lockerMaxItems
            ? (int)$lockerMaxItems
            : CarrierConstants::DEFAULT_VALUE_LOCKER_MAX_ITEMS;
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    private function resolveIsTesting(array $options): bool
    {
        $isTesting = $options[self::IS_TESTING] ?? null;

        return $isTesting === 'yes' || $isTesting === '1' || (int)$isTesting === 1;
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function resolveHostCountry(array $options): string
    {
        $hostCountry = $options[self::HOST_COUNTRY] ?? null;

        return is_string($hostCountry) && $hostCountry !== '' && $hostCountry !== 'none'
            ? $hostCountry
            : CarrierConstants::API_HOST_LOCALE_RO;
    }

    /**
     * @param array $options
     * @param string $key
     *
     * @return string|null
     */
    private function resolveNullableString(array $options, string $key): ?string
    {
        $value = $options[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param array $options
     * @param string $key
     *
     * @return bool
     */
    private function resolveEnabledUnlessNo(array $options, string $key): bool
    {
        $value = $options[$key] ?? null;

        return !(null === $value || 'no' === $value);
    }
}
