<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses;

if (!defined('ABSPATH')) {
    exit;
}

class AwbGenerateServiceTaxResponse
{
    /**
     * @var array $serviceTaxIds
     */
    private array $serviceTaxIds;

    /**
     * @param array $serviceTaxIds
     */
    public function __construct(array $serviceTaxIds)
    {
        $this->serviceTaxIds = $serviceTaxIds;
    }

    /**
     * @return array
     */
    public function getServiceTaxIds(): array
    {
        return $this->serviceTaxIds;
    }
}
