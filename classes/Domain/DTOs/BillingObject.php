<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use SamedayCourier\Shipping\Domain\DTOs\Traits\AddressObjectTrait;

if (!defined('ABSPATH')) {
    exit;
}

final class BillingObject
{
    use AddressObjectTrait;
}
