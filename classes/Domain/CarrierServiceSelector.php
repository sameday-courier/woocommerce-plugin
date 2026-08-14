<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CarrierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;

final class CarrierServiceSelector
{
    /**
     * @var CarrierServiceProviderInterface
     */
    private CarrierServiceProviderInterface $carrierServiceProvider;

    /**
     * @var CarrierSettingsProviderInterface
     */
    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    public function __construct(
        CarrierServiceProviderInterface  $carrierServiceProvider,
        CarrierSettingsProviderInterface $carrierSettingsProvider
    )
    {
        $this->carrierServiceProvider = $carrierServiceProvider;
        $this->carrierSettingsProvider = $carrierSettingsProvider;
    }

    /**
     * @param string $destinationCountry
     *
     * @return CarrierService[]
     */
    public function getEligibleServices(string $destinationCountry): array
    {
        $hostCountry = $this->carrierSettingsProvider->get()->getHostCountry();
        $eligibleShippingServices = CarrierConstants::ELIGIBLE_SERVICES;
        if ($destinationCountry !== $hostCountry) {
            $eligibleShippingServices = CarrierConstants::CROSSBORDER_ELIGIBLE_SERVICES;
        }

        return array_filter(
            $this->carrierServiceProvider->getAvailableServices(),
            static function (CarrierService $service) use ($eligibleShippingServices) {
                return in_array(
                    $service->getSamedayCode(),
                    $eligibleShippingServices,
                    true
                );
            }
        );
    }
}
