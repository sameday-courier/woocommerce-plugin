<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

class CarrierCurrencyRules
{
    /**
     * Sameday operates only in RO, BG and HU, so any other country has no currency in this domain.
     *
     * @param string|null $country
     *
     * @return string|null
     */
    public static function resolveForCountry(?string $country): ?string
    {
        if (null === $country || '' === $country) {
            return null;
        }

        return CarrierConstants::CURRENCY_MAPPER[$country] ?? null;
    }

    /**
     * @param string $country
     *
     * @return string
     */
    public static function resolveForCountryRequired(string $country): string
    {
        $currency = self::resolveForCountry($country);

        if (null === $currency) {
            throw new \InvalidArgumentException(
                sprintf('No currency configured for country "%s".', $country)
            );
        }

        return $currency;
    }

    /**
     * @return array<int, string>
     */
    public static function supportedCountries(): array
    {
        return array_keys(CarrierConstants::CURRENCY_MAPPER);
    }

    /**
     * @param float $repayment
     * @param string $destCurrency
     * @param string $storeCurrency
     *
     * @return bool
     */
    public static function hasCurrencyIssue(
        float $repayment,
        string $destCurrency,
        string $storeCurrency
    ): bool {
        if ($repayment === .0) {
            return false;
        }

        if ($destCurrency === $storeCurrency) {
            return false;
        }

        return true;
    }
}
