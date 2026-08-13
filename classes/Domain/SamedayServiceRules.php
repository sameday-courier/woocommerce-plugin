<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\Ports\SamedayServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Text\RomanianDiacriticsNormalizer;

final class SamedayServiceRules
{
    /**
     * @var SamedayServiceProviderInterface
     */
    private SamedayServiceProviderInterface $samedayServiceProvider;

    /**
     * @param SamedayServiceProviderInterface $samedayServiceProvider
     */
    public function __construct(
        SamedayServiceProviderInterface $samedayServiceProvider
    )
    {
        $this->samedayServiceProvider = $samedayServiceProvider;
    }

    /**
     * @param SamedayService $samedayService
     *
     * @return bool
     */
    public function isEligibleToLockerFirstMile(SamedayService $samedayService): bool
    {
        $optionalServices = $this->samedayServiceProvider->getServiceIdOptionalTaxes($samedayService->getSamedayId());

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
     * @param SamedayService $samedayService
     *
     * @return bool
     */
    public function isEasyBoxServiceType(SamedayService $samedayService): bool
    {
        return in_array($samedayService->getSamedayCode(), SamedayConstants::EASYBOX_TYPE_SERVICE, true);
    }

    /**
     * @param SamedayService $samedayService
     *
     * @return bool
     */
    public function isPudoServiceType(SamedayService $samedayService): bool
    {
        return in_array($samedayService->getSamedayCode(), SamedayConstants::PUDO_TYPE_SERVICE, true);
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
     * @param string|null $stateName
     *
     * @return bool
     */
    public function isEligibleTo6H(SamedayService $samedayService, ?string $stateName): bool
    {
        $is6HCode = $samedayService->getSamedayCode() === SamedayConstants::SAMEDAY_6H_CODE;

        $isEligibleRegionFor6H = in_array(
            RomanianDiacriticsNormalizer::normalize($stateName ?? ''),
            SamedayConstants::ELIGIBLE_TO_6H_SERVICE,
            true
        );

        return !($is6HCode && ($isEligibleRegionFor6H === false));
    }
}
