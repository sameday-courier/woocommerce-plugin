<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class BillingObject
{
    use AddressTrait;

    private ?string $email;

    private ?string $phone;

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
     * @param string|null $email
     * @param string|null $phone
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
        ?string $country = null,
        ?string $email = null,
        ?string $phone = null
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
        $this->email = $email;
        $this->phone = $phone;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ...array_merge(
                self::mapAddressFromArray($data),
                [
                    isset($data['email']) ? (string) $data['email'] : null,
                    isset($data['phone']) ? (string) $data['phone'] : null,
                ]
            )
        );
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
