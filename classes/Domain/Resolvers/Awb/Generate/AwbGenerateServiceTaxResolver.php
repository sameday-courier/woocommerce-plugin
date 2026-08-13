<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate;

use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\Ports\SamedayServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses\AwbGenerateServiceTaxResponse;
use SamedayCourier\Shipping\Domain\SamedayConstants;

class AwbGenerateServiceTaxResolver
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
     * @param bool $hasOpenPackage
     * @param bool $hasLockerFirstMile
     * @param int $packageType
     *
     * @return AwbGenerateServiceTaxResponse
     */
    public function resolve(
        SamedayService $samedayService,
        bool           $hasOpenPackage,
        bool           $hasLockerFirstMile,
        int            $packageType
    ): AwbGenerateServiceTaxResponse
    {
        $optionalServices = $this->samedayServiceProvider->getServiceIdOptionalTaxes($samedayService->getSamedayId());

        $serviceTaxIds = [];
        if ($hasOpenPackage) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::OPEN_PACKAGE_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $packageType
                ) {
                    $serviceTaxIds[] = SamedayConstants::OPEN_PACKAGE_OPTION_CODE;

                    break;
                }
            }
        }

        if ($hasLockerFirstMile) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $packageType
                ) {
                    $serviceTaxIds[] = SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE;
                    break;
                }
            }
        }

        return new AwbGenerateServiceTaxResponse($serviceTaxIds);
    }
}
