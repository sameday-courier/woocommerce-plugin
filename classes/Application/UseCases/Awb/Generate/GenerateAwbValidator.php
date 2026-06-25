<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\Awb\Generate\GenerateAwbOrderRules;
use SamedayCourier\Shipping\Domain\Awb\Generate\GenerateAwbRecipientRules;
use SamedayCourier\Shipping\Domain\Awb\Generate\ValidationResult;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbValidator
{
    private SamedayServiceRepository $samedayServiceRepository;

    public function __construct(SamedayServiceRepository $samedayServiceRepository)
    {
        $this->samedayServiceRepository = $samedayServiceRepository;
    }

    public function validate(
        GenerateAwbItem $item
    ): ValidationResult
    {
        $result = GenerateAwbOrderRules::validateShippingLines($item->getShippingLines());
        $result = $result->merge(
            GenerateAwbRecipientRules::validate($item->getShipping(), $item->getBilling())
        );

        if ([] === OptionsHandler::getSamedayOptions()) {
            $result = $result->merge(new ValidationResult(['No sameday options available.']));
        }

        if (null === $this->samedayServiceRepository->getServiceSameday($item->getServiceId())) {
            $result = $result->merge(new ValidationResult(['Selected service could not be found.']));
        }

        return $result;
    }
}
