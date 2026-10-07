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
     *
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
     * Checkout offers one out-of-home rate per lane (LN at home, XL across the border).
     * The map selection decides the AWB code: easybox stays on that rate, a Sameday point
     * uses the point service of the same lane (PP or XP).
     *
     * @param string $orderServiceCode
     * @param string|null $oohType 0/LN/XL for an easybox, 1/PP/XP for a Sameday point
     *
     * @return string
     */
    public function resolveAwbServiceCode(string $orderServiceCode, ?string $oohType): string
    {
        $lane = $this->oohLane($orderServiceCode);
        if (null === $lane) {
            return $orderServiceCode;
        }

        if ($this->isSamedayPoint($oohType)) {
            return $lane['point'];
        }

        return $lane['easybox'];
    }

    /**
     * Rates the customer can choose. Point codes are resolved later, at AWB generation.
     *
     * @param string $serviceCode
     *
     * @return bool
     */
    public function isCheckoutOohCode(string $serviceCode): bool
    {
        return in_array($serviceCode, [
            CarrierConstants::LOCKER_NEXT_DAY_CODE,
            CarrierConstants::LOCKER_CROSSBORDER_CODE,
        ], true);
    }

    /**
     * @param string|null $oohType
     *
     * @return bool
     */
    private function isSamedayPoint(?string $oohType): bool
    {
        if (null === $oohType || '' === $oohType) {
            return false;
        }

        if ('1' === $oohType) {
            return true;
        }

        return in_array($oohType, [
            CarrierConstants::PUDO_CODE,
            CarrierConstants::CROSSBORDER_PUDO,
        ], true);
    }

    /**
     * @param string $serviceCode
     *
     * @return array{easybox: string, point: string}|null
     */
    private function oohLane(string $serviceCode): ?array
    {
        if (
            in_array($serviceCode, [
                CarrierConstants::LOCKER_NEXT_DAY_CODE,
                CarrierConstants::PUDO_CODE,
            ], true)
        ) {
            return [
                'easybox' => CarrierConstants::LOCKER_NEXT_DAY_CODE,
                'point' => CarrierConstants::PUDO_CODE,
            ];
        }

        if (
            in_array($serviceCode, [
                CarrierConstants::LOCKER_CROSSBORDER_CODE,
                CarrierConstants::CROSSBORDER_PUDO,
            ], true)
        ) {
            return [
                'easybox' => CarrierConstants::LOCKER_CROSSBORDER_CODE,
                'point' => CarrierConstants::CROSSBORDER_PUDO,
            ];
        }

        return null;
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
