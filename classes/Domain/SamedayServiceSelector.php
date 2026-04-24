<?php

namespace SamedayCourier\Shipping\Domain;

use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Utils\Helper;

final class SamedayServiceSelector
{
    /**
     * @var SamedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    public function __construct(SamedayServiceRepository $samedayServiceRepository)
    {
        $this->samedayServiceRepository = $samedayServiceRepository;
    }

    /**
     * @param string $destinationCountry
     *
     * @return SamedayService[]
     */
    public function getEligibleServices(string $destinationCountry): array
    {
        $hostCountry = Helper::getHostCountry();
        $eligibleShippingServices = SamedayConstants::ELIGIBLE_SERVICES;
        if ($destinationCountry !== $hostCountry) {
            $eligibleShippingServices = SamedayConstants::CROSSBORDER_ELIGIBLE_SERVICES;
        }

        return array_filter(
            $this->samedayServiceRepository->getAvailableServices(),
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
