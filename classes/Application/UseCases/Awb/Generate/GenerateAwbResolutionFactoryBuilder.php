<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\Awb\Generate\GenerateAwbResolutionFactory;
use SamedayCourier\Shipping\Domain\Awb\Generate\Resolvers\GenerateAwbOohDeliveryResolver;
use SamedayCourier\Shipping\Domain\Awb\Generate\Resolvers\GenerateAwbOptionalTaxesResolver;
use SamedayCourier\Shipping\Domain\Awb\Generate\Resolvers\GenerateAwbRecipientResolver;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbResolutionFactoryBuilder
{
    public static function build(SamedayServiceRepository $samedayServiceRepository): GenerateAwbResolutionFactory
    {
        return new GenerateAwbResolutionFactory(
            new GenerateAwbRecipientResolver(),
            new GenerateAwbOohDeliveryResolver(),
            new GenerateAwbOptionalTaxesResolver(new SamedayOptionalTaxesProvider($samedayServiceRepository)),
        );
    }
}
