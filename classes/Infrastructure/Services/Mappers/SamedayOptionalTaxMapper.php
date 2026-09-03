<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use Sameday\Objects\Service\OptionalTaxObject;
use SamedayCourier\Shipping\Domain\DTOs\CarrierOptionalTaxDto;

final class SamedayOptionalTaxMapper
{
    /**
     * @param OptionalTaxObject $optionalTax
     *
     * @return CarrierOptionalTaxDto
     */
    public function map(OptionalTaxObject $optionalTax): CarrierOptionalTaxDto
    {
        return new CarrierOptionalTaxDto(
            $optionalTax->getId(),
            $optionalTax->getCode(),
            $optionalTax->getPackageType()->getType()
        );
    }

    /**
     * @param list<OptionalTaxObject> $optionalTaxes
     *
     * @return list<CarrierOptionalTaxDto>
     */
    public function mapCollection(array $optionalTaxes): array
    {
        return array_map(
            function (OptionalTaxObject $optionalTax): CarrierOptionalTaxDto {
                return $this->map($optionalTax);
            },
            $optionalTaxes
        );
    }
}
