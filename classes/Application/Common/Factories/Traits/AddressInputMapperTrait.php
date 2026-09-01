<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories\Traits;

trait AddressInputMapperTrait
{
    /** @var list<string> */
    private static array $addressInputKeys = [
        'first_name',
        'last_name',
        'company',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'country',
        'email',
        'phone',
    ];

    /**
     * @param array $raw
     *
     * @return array<int, string|null>
     */
    protected function mapAddressInput(array $raw): array
    {
        $mapped = [];

        foreach (self::$addressInputKeys as $key) {
            if (!isset($raw[$key])) {
                $mapped[] = null;

                continue;
            }

            $value = (string) $raw[$key];
            $mapped[] = '' !== $value ? $value : null;
        }

        return $mapped;
    }
}
