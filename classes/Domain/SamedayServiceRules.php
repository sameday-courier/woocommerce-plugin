<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

if (!defined('ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\Text\RomanianDiacriticsNormalizer;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class SamedayServiceRules
{
    /**
     * @var SamedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param SamedayServiceRepository $samedayServiceRepository
     */
    public function __construct(
        SamedayServiceRepository $samedayServiceRepository
    )
    {
        $this->samedayServiceRepository = $samedayServiceRepository;
    }

    /**
     * @param SamedayService $samedayService
     *
     * @return bool
     */
    public function isEligibleToLockerFirstMile(SamedayService $samedayService): bool
    {
        $optionalServices = $this->samedayServiceRepository->getServiceIdOptionalTaxes($samedayService->getSamedayId());

        foreach ($optionalServices as $optionalService) {
            if ($optionalService->getCode() === SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SamedayService $samedayService
     * @return bool
     */
    public function isOohDeliveryOption(SamedayService $samedayService): bool
    {
        return $this->isOohDeliveryOptionByCode($samedayService->getSamedayCode());
    }

    /**
     * @param string $samedayServiceCode
     *
     * @return bool
     */
    public function isOohDeliveryOptionByCode(string $samedayServiceCode): bool
    {
        return in_array($samedayServiceCode, SamedayConstants::OOH_SERVICES, true);
    }

    /**
     * @param SamedayService $samedayService
     * @param string $stateName
     *
     * @return bool
     */
    public function isEligibleTo6H(SamedayService $samedayService, string $stateName): bool
    {
        $is6HCode = $samedayService->getSamedayCode() === SamedayConstants::SAMEDAY_6H_CODE;

        $isEligibleRegionFor6H = in_array(
            RomanianDiacriticsNormalizer::normalize($stateName),
            SamedayConstants::ELIGIBLE_TO_6H_SERVICE,
            true
        );

        return !($is6HCode && ($isEligibleRegionFor6H === false));
    }
}
