<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class ShippingObject
{
    use AddressTrait;

    /**
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $company
     * @param string|null $address1
     * @param string|null $address2
     * @param string|null $city
     * @param string|null $state
     * @param string|null $postcode
     * @param string|null $country
     */
    public function __construct(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $company = null,
        ?string $address1 = null,
        ?string $address2 = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postcode = null,
        ?string $country = null
    ) {
        $this->initializeAddress(
            $firstName,
            $lastName,
            $company,
            $address1,
            $address2,
            $city,
            $state,
            $postcode,
            $country
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(...self::mapAddressFromArray($data));
    }
}
