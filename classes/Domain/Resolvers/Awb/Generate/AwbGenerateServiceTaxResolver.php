<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses\AwbGenerateServiceTaxResponse;
use SamedayCourier\Shipping\Domain\SamedayConstants;

if (!defined('ABSPATH')) {
    exit;
}

class AwbGenerateServiceTaxResolver
{
    private SamedayServiceRepository $samedayServiceRepository;

    public function __construct(SamedayServiceRepository $samedayServiceRepository)
    {
        $this->samedayServiceRepository = $samedayServiceRepository;
    }

    public function resolve(
        SamedayService $samedayService,
        GenerateAwbItem $awbItem
    ): AwbGenerateServiceTaxResponse
    {
        $optionalServices = $this->samedayServiceRepository->getServiceIdOptionalTaxes(
            $samedayService->getSamedayId()
        );

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
