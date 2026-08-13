<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories;

use SamedayCourier\Shipping\Application\Common\Factories\Traits\AddressInputMapperTrait;
use SamedayCourier\Shipping\Domain\DTOs\ShippingDto;

final class ShippingDtoFactory
{
    use AddressInputMapperTrait;

    /**
     * @param array<string, mixed> $raw
     */
    public function fromInput(array $raw): ShippingDto
    {
        return new ShippingDto(...$this->mapAddressInput($raw));
    }
}
