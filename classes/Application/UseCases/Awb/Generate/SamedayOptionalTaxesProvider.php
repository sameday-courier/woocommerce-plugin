<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\Awb\Generate\Ports\SamedayOptionalTaxesProviderInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class SamedayOptionalTaxesProvider implements SamedayOptionalTaxesProviderInterface
{
    private SamedayServiceRepository $samedayServiceRepository;

    public function __construct(SamedayServiceRepository $samedayServiceRepository)
    {
        $this->samedayServiceRepository = $samedayServiceRepository;
    }

    public function getOptionalTaxesForService(int $samedayServiceId): array
    {
        return $this->samedayServiceRepository->getServiceIdOptionalTaxes($samedayServiceId);
    }
}
