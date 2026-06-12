<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use Sameday\Sameday;

if (!defined('ABSPATH')) {
    exit;
}

final class GetCitiesRequest
{
    /**
     * @var Sameday $sameday
     */
    public Sameday $sameday;

    /**
     * @var int $countyId
     */
    public int $countyId;

    public function __construct(
        Sameday $sameday,
        int $countyId
    )
    {
        $this->sameday = $sameday;
        $this->countyId = $countyId;
    }
}
