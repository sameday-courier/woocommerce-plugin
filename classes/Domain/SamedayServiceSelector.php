<?php

namespace SamedayCourier\Shipping\Domain;

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
     * @return array
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
            static function ($row) use ($eligibleShippingServices) {
                return in_array(
                    $row['sameday_code'] ?? '',
                    $eligibleShippingServices,
                    true
                );
            }
        );
    }
}
