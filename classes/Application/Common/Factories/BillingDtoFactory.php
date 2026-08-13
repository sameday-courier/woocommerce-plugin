<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories;

use SamedayCourier\Shipping\Application\Common\Factories\Traits\AddressInputMapperTrait;
use SamedayCourier\Shipping\Domain\DTOs\BillingDto;

final class BillingDtoFactory
{
    use AddressInputMapperTrait;

    /**
     * @param array<string, mixed> $raw
     */
    public function fromInput(array $raw): BillingDto
    {
        return new BillingDto(...$this->mapAddressInput($raw));
    }
}
