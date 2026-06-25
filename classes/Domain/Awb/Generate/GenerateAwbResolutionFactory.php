<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate;

use JsonException;
use SamedayCourier\Shipping\Domain\Awb\Generate\Resolvers\GenerateAwbOohDeliveryResolver;
use SamedayCourier\Shipping\Domain\Awb\Generate\Resolvers\GenerateAwbOptionalTaxesResolver;
use SamedayCourier\Shipping\Domain\Awb\Generate\Resolvers\GenerateAwbRecipientResolver;
use SamedayCourier\Shipping\Domain\Models\SamedayService;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbResolutionFactory
{
    private GenerateAwbRecipientResolver $recipientResolver;

    private GenerateAwbOohDeliveryResolver $oohDeliveryResolver;

    private GenerateAwbOptionalTaxesResolver $optionalTaxesResolver;

    public function __construct(
        GenerateAwbRecipientResolver $recipientResolver,
        GenerateAwbOohDeliveryResolver $oohDeliveryResolver,
        GenerateAwbOptionalTaxesResolver $optionalTaxesResolver
    ) {
        $this->recipientResolver = $recipientResolver;
        $this->oohDeliveryResolver = $oohDeliveryResolver;
        $this->optionalTaxesResolver = $optionalTaxesResolver;
    }

    /**
     * @throws JsonException
     */
    public function create(GenerateAwbContext $context, SamedayService $service): GenerateAwbResolution
    {
        return new GenerateAwbResolution(
            $this->recipientResolver->resolve($context, $service),
            $this->oohDeliveryResolver->resolve($context, $service),
            $this->optionalTaxesResolver->resolve($context, $service),
        );
    }
}
