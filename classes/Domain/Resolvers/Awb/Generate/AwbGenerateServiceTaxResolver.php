<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate;

use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CarrierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses\AwbGenerateServiceTaxResponse;
use SamedayCourier\Shipping\Domain\CarrierConstants;

class AwbGenerateServiceTaxResolver
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
     * @param bool $hasOpenPackage
     * @param bool $hasLockerFirstMile
     * @param int $packageType
     *
     * @return AwbGenerateServiceTaxResponse
     */
    public function resolve(
        CarrierService $carrierService,
        bool $hasOpenPackage,
        bool $hasLockerFirstMile,
        int $packageType
    ): AwbGenerateServiceTaxResponse {
        $optionalServices = $this->carrierServiceProvider->getServiceIdOptionalTaxes($carrierService->getSamedayId());

        $serviceTaxIds = [];
        if ($hasOpenPackage) {
            foreach ($optionalServices as $optionalService) {
                if (
                    $optionalService->getCode() === CarrierConstants::OPEN_PACKAGE_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $packageType
                ) {
                    $serviceTaxIds[] = CarrierConstants::OPEN_PACKAGE_OPTION_CODE;

                    break;
                }
            }
        }

        if ($hasLockerFirstMile) {
            foreach ($optionalServices as $optionalService) {
                if (
                    $optionalService->getCode() === CarrierConstants::PERSONAL_DELIVERY_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $packageType
                ) {
                    $serviceTaxIds[] = CarrierConstants::PERSONAL_DELIVERY_OPTION_CODE;
                    break;
                }
            }
        }

        return new AwbGenerateServiceTaxResponse($serviceTaxIds);
    }
}
