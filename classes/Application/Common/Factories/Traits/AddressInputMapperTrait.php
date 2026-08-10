<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories\Traits;

if (!defined('ABSPATH')) {
    exit;
}

trait AddressInputMapperTrait
{
    /**
     * @param array<string, mixed> $raw
     *
     * @return array<int, string|null>
     */
    protected function mapAddressInput(array $raw): array
    {
        return [
            isset($raw['first_name']) ? (string) $raw['first_name'] : null,
            isset($raw['last_name']) ? (string) $raw['last_name'] : null,
            isset($raw['company']) ? (string) $raw['company'] : null,
            isset($raw['address_1']) ? (string) $raw['address_1'] : null,
            isset($raw['address_2']) ? (string) $raw['address_2'] : null,
            isset($raw['city']) ? (string) $raw['city'] : null,
            isset($raw['state']) ? (string) $raw['state'] : null,
            isset($raw['postcode']) ? (string) $raw['postcode'] : null,
            isset($raw['country']) ? (string) $raw['country'] : null,
            isset($raw['email']) ? (string) $raw['email'] : null,
            isset($raw['phone']) ? (string) $raw['phone'] : null,
        ];
    }
}
