<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\ValueObject\Address;

use SamedayCourier\Shipping\Domain\DTOs\PostalCodeDto;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;

final class PostalCode
{
    private function __construct()
    {
    }

    /**
     * @param string|null $code
     * @param string $countyCode
     * @param string $countryCode
     * @param CityPostalCodeProviderInterface $cityPostalCodeProvider
     *
     * @return PostalCodeDto
     */
    public static function tryCreate(
        ?string $code,
        string $countyCode,
        string $countryCode,
        CityPostalCodeProviderInterface $cityPostalCodeProvider
    ): PostalCodeDto {
        if (null === $code || '' === $code || '' === $countyCode || '' === $countryCode) {
            return new PostalCodeDto(null);
        }

        $reference = $cityPostalCodeProvider->getPostalForSpecificCounty(
            $countyCode,
            $countryCode
        );

        if (null === $reference) {
            return new PostalCodeDto(null);
        }

        if (mb_strlen($reference) !== mb_strlen($code)) {
            return new PostalCodeDto(null);
        }

        if ($code[0] !== $reference[0]) {
            return new PostalCodeDto(null);
        }

        return new PostalCodeDto($code);
    }
}
