<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use Sameday\Sameday;

if (!defined('ABSPATH')) {
    exit;
}

final class DeletePickupPointRequest
{
    /**
     * @var Sameday $sameday
     */
    public Sameday $sameday;

    /**
     * @var int $samedayId
     */
    public int $samedayId;

    public function __construct(
        Sameday $sameday,
        int $samedayId
    )
    {
        $this->sameday = $sameday;
        $this->samedayId = $samedayId;
    }
}
