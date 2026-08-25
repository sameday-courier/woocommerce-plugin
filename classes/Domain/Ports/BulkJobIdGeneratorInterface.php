<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\ValueObject\BulkJobId;

interface BulkJobIdGeneratorInterface
{
    /**
     * @return BulkJobId
     */
    public function generate(): BulkJobId;
}
