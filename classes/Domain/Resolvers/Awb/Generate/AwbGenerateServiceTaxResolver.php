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
    /**
     * @var SamedayService $samedayService
     */
    private SamedayService $samedayService;

    /**
     * @var SamedayServiceRepository $samedayRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var GenerateAwbItem $generateAwbItem
     */
    private GenerateAwbItem $awbItem;

    public function __construct(
        SamedayService $samedayService,
        SamedayServiceRepository $samedayServiceRepository,
        GenerateAwbItem $awbItem
    )
    {
        $this->samedayService = $samedayService;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->awbItem = $awbItem;
    }

    /**
     * @return AwbGenerateServiceTaxResponse
     */
    public function resolve(): AwbGenerateServiceTaxResponse
    {
        $optionalServices = $this->samedayServiceRepository->getServiceIdOptionalTaxes(
            $this->samedayService->getSamedayId()
        );

        $item = $this->awbItem;

        $serviceTaxIds = [];
        if ($item->hasOpenPackage()) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::OPEN_PACKAGE_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $item->getPackageType()
                ) {
                    $serviceTaxIds[] = SamedayConstants::OPEN_PACKAGE_OPTION_CODE;

                    break;
                }
            }
        }

        if ($item->hasLockerFirstMile()) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $item->getPackageType()
                ) {
                    $serviceTaxIds[] = SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE;
                    break;
                }
            }
        }

        return new AwbGenerateServiceTaxResponse($serviceTaxIds);
    }
}
