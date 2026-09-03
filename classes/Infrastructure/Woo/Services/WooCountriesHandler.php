<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\CountriesHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\WooCommerceHandlerInterface;

final class WooCountriesHandler implements CountriesHandlerInterface
{
    /**
     * @var WooCommerceHandlerInterface $wooCommerceHandler
     */
    private WooCommerceHandlerInterface $wooCommerceHandler;

    /**
     * @param WooCommerceHandlerInterface|null $wooCommerceHandler
     */
    public function __construct(?WooCommerceHandlerInterface $wooCommerceHandler = null)
    {
        $this->wooCommerceHandler = $wooCommerceHandler ?? new WooHandler();
    }

    /**
     * @return array<string, string>
     */
    public function getShippingCountries(): array
    {
        return $this->wooCommerceHandler->getWC()->countries->get_shipping_countries();
    }

    /**
     * @param string $countryCode
     *
     * @return array<string, string>|null
     */
    public function getStatesForCountry(string $countryCode): ?array
    {
        $states = $this->getAllStates()[$countryCode] ?? null;

        return is_array($states) ? $states : null;
    }

    /**
     * @param string $countryCode
     * @param string $stateCode
     *
     * @return string
     */
    public function getStateName(string $countryCode, string $stateCode): string
    {
        $states = $this->getStatesForCountry($countryCode);

        if (null === $states) {
            return '';
        }

        return ($states[$stateCode] ?? '');
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getAllStates(): array
    {
        return $this->wooCommerceHandler->getWC()->countries->get_states();
    }
}
