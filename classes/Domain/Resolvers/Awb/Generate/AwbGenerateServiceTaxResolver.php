<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate;

use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses\AwbGenerateServiceTaxResponse;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;

if (!defined('ABSPATH')) {
    exit;
}

class AwbGenerateServiceTaxResolver
{
    /**
     * @var SamedayServiceRules $samedayServiceRules
     */
    private SamedayServiceRules $samedayServiceRules;

    /**
     * @param SamedayServiceRules $samedayServiceRules
     */
    public function __construct(
        SamedayServiceRules $samedayServiceRules
    )
    {
        $this->samedayServiceRules = $samedayServiceRules;
    }

    /**
     * @param SamedayService $samedayService
     * @param GenerateAwbItem $awbItem
     *
     * @return AwbGenerateServiceTaxResponse
     */
    public function resolve(
        SamedayService $samedayService,
        GenerateAwbItem $awbItem
    ): AwbGenerateServiceTaxResponse
    {
        $optionalServices = $this->samedayServiceRules
            ->getSamedayServiceRepository()
            ->getServiceIdOptionalTaxes($samedayService->getSamedayId());

        $serviceTaxIds = [];
        if ($awbItem->hasOpenPackage()) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::OPEN_PACKAGE_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $awbItem->getPackageType()
                ) {
                    $serviceTaxIds[] = SamedayConstants::OPEN_PACKAGE_OPTION_CODE;

                    break;
                }
            }
        }

        if ($awbItem->hasLockerFirstMile()) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $awbItem->getPackageType()
                ) {
                    $serviceTaxIds[] = SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE;
                    break;
                }
            }
        }

        return new AwbGenerateServiceTaxResponse($serviceTaxIds);
    }
}
