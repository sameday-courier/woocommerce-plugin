<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate\Resolvers;

use SamedayCourier\Shipping\Domain\Awb\Generate\GenerateAwbContext;
use SamedayCourier\Shipping\Domain\Awb\Generate\Ports\SamedayOptionalTaxesProviderInterface;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\SamedayConstants;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbOptionalTaxesResolver
{
    private SamedayOptionalTaxesProviderInterface $optionalTaxesProvider;

    public function __construct(SamedayOptionalTaxesProviderInterface $optionalTaxesProvider)
    {
        $this->optionalTaxesProvider = $optionalTaxesProvider;
    }

    /**
     * @return string[]
     */
    public function resolve(GenerateAwbContext $context, SamedayService $service): array
    {
        $optionalServices = $this->optionalTaxesProvider->getOptionalTaxesForService(
            $service->getSamedayId()
        );
        $serviceTaxIds = [];

        if ($context->hasOpenPackage()) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::OPEN_PACKAGE_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $context->getPackageType()
                ) {
                    $serviceTaxIds[] = SamedayConstants::OPEN_PACKAGE_OPTION_CODE;

                    break;
                }
            }
        }

        if ($context->hasLockerFirstMile()) {
            foreach ($optionalServices as $optionalService) {
                if ($optionalService->getCode() === SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE
                    && $optionalService->getPackageType()->getType() === $context->getPackageType()
                ) {
                    $serviceTaxIds[] = SamedayConstants::PERSONAL_DELIVERY_OPTION_CODE;
                    break;
                }
            }
        }

        return $serviceTaxIds;
    }
}
