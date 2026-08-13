<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\ValueObject\Address;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Domain\DTOs\PostalCodeDto;

final class PostalCode
{
    private function __construct()
    {
    }

    /**
     * @param string|null $code
     * @param string $countyCode
     * @param string $countryCode
     *
     * @return PostalCodeDto|null
     */
    public static function tryCreate(
        ?string $code,
        string $countyCode,
        string $countryCode
    ): PostalCodeDto {
        if (null === $code || '' === $code || '' === $countyCode || '' === $countryCode) {
            return new PostalCodeDto(null);
        }

        $reference = (new SamedayCityRepository())->getPostalForSpecificCounty(
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
