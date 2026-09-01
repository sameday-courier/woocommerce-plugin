<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\Ports\BulkJobIdGeneratorInterface;
use SamedayCourier\Shipping\Domain\ValueObject\BulkJobId;

final class BulkJobIdGeneratorServiceProvider implements BulkJobIdGeneratorInterface
{
    /**
     * @return BulkJobId
     */
    public function generate(): BulkJobId
    {
        return BulkJobId::fromString(wp_generate_uuid4());
    }
}
