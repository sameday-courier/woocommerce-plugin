<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain;

use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CarrierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Text\RomanianDiacriticsNormalizer;

final class CarrierServiceRules
{
    /**
     * @var CarrierServiceProviderInterface
     */
    private CarrierServiceProviderInterface $carrierServiceProvider;

    /**
     * @param CarrierServiceProviderInterface $carrierServiceProvider
     */
    public function __construct(
        CarrierServiceProviderInterface $carrierServiceProvider
    ) {
        $this->carrierServiceProvider = $carrierServiceProvider;
    }

    /**
     * @param CarrierService $carrierService
     *
     * @return bool
     */
    public function isEligibleToLockerFirstMile(CarrierService $carrierService): bool
    {
        $optionalServices = $this->carrierServiceProvider->getServiceIdOptionalTaxes($carrierService->getSamedayId());

        foreach ($optionalServices as $optionalService) {
            if ($optionalService->getCode() === CarrierConstants::PERSONAL_DELIVERY_OPTION_CODE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CarrierService $carrierService
     * @return bool
     */
    public function isOohDeliveryOption(CarrierService $carrierService): bool
    {
        return $this->isOohDeliveryOptionByCode($carrierService->getSamedayCode());
    }

    /**
     * @param CarrierService $carrierService
     *
     * @return bool
     */
    public function isEasyBoxServiceType(CarrierService $carrierService): bool
    {
        return in_array($carrierService->getSamedayCode(), CarrierConstants::EASYBOX_TYPE_SERVICE, true);
    }

    /**
     * @param CarrierService $carrierService
     *
     * @return bool
     */
    public function isPudoServiceType(CarrierService $carrierService): bool
    {
        return in_array($carrierService->getSamedayCode(), CarrierConstants::PUDO_TYPE_SERVICE, true);
    }

    /**
     * @param string $samedayServiceCode
     *
     * @return bool
     */
    public function isOohDeliveryOptionByCode(string $samedayServiceCode): bool
    {
        return in_array($samedayServiceCode, CarrierConstants::OOH_SERVICES, true);
    }

    /**
     * @param CarrierService $carrierService
     * @param string|null $stateName
     *
     * @return bool
     */
    public function isEligibleTo6H(CarrierService $carrierService, ?string $stateName): bool
    {
        $is6HCode = $carrierService->getSamedayCode() === CarrierConstants::SAMEDAY_6H_CODE;

        $isEligibleRegionFor6H = in_array(
            RomanianDiacriticsNormalizer::normalize($stateName ?? ''),
            CarrierConstants::ELIGIBLE_TO_6H_SERVICE,
            true
        );

        return !($is6HCode && ($isEligibleRegionFor6H === false));
    }
}
