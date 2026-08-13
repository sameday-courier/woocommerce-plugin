<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\Ports\SamedayServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\SamedaySettingsProviderInterface;

final class SamedayServiceSelector
{
    /**
     * @var SamedayServiceProviderInterface
     */
    private SamedayServiceProviderInterface $samedayServiceProvider;

    /**
     * @var SamedaySettingsProviderInterface
     */
    private SamedaySettingsProviderInterface $samedaySettingsProvider;

    public function __construct(
        SamedayServiceProviderInterface  $samedayServiceProvider,
        SamedaySettingsProviderInterface $samedaySettingsProvider
    )
    {
        $this->samedayServiceProvider = $samedayServiceProvider;
        $this->samedaySettingsProvider = $samedaySettingsProvider;
    }

    /**
     * @param string $destinationCountry
     *
     * @return SamedayService[]
     */
    public function getEligibleServices(string $destinationCountry): array
    {
        $hostCountry = $this->samedaySettingsProvider->get()->getHostCountry();
        $eligibleShippingServices = SamedayConstants::ELIGIBLE_SERVICES;
        if ($destinationCountry !== $hostCountry) {
            $eligibleShippingServices = SamedayConstants::CROSSBORDER_ELIGIBLE_SERVICES;
        }

        return array_filter(
            $this->samedayServiceProvider->getAvailableServices(),
            static function (SamedayService $service) use ($eligibleShippingServices) {
                return in_array(
                    $service->getSamedayCode(),
                    $eligibleShippingServices,
                    true
                );
            }
        );
    }
}
