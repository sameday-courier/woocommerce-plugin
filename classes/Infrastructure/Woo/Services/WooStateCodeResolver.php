<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\CountriesHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\Text\RomanianDiacriticsNormalizer;

final class WooStateCodeResolver implements StateCodeResolverInterface
{
    /**
     * @var CountriesHandlerInterface $countriesHandler
     */
    private CountriesHandlerInterface $countriesHandler;

    /**
     * @param CountriesHandlerInterface $countriesHandler
     */
    public function __construct(CountriesHandlerInterface $countriesHandler)
    {
        $this->countriesHandler = $countriesHandler;
    }

    /**
     * @param string|null $countryCode
     * @param string|null $stateCode
     *
     * @return string|null
     */
    public function resolveNameFromCode(?string $countryCode, ?string $stateCode): ?string
    {
        if (null === $countryCode || null === $stateCode || '' === $countryCode || '' === $stateCode) {
            return null;
        }

        $name = html_entity_decode($this->countriesHandler->getStateName($countryCode, $stateCode));

        return '' === $name ? null : $name;
    }

    /**
     * @param string $countryCode
     * @param string $stateName
     *
     * @return string
     */
    public function resolveFromName(string $countryCode, string $stateName): string
    {
        if ('' === $countryCode || '' === $stateName) {
            return '';
        }

        $states = $this->countriesHandler->getStatesForCountry($countryCode);

        if (null === $states) {
            return '';
        }

        foreach ($states as $code => $name) {
            $normalizedName = RomanianDiacriticsNormalizer::normalize($name);
            $normalizedStateName = RomanianDiacriticsNormalizer::normalize($stateName);
            if ($normalizedName === $normalizedStateName) {
                return (string) $code;
            }
        }

        return '';
    }
}
