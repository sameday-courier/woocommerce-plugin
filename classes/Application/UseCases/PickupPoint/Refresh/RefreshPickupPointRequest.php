<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshPickupPointRequest
{
    /**
     * @var Sameday $sameday
     */
    public Sameday $sameday;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    public SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @param SamedayPickupPointRepository $pickupPointRepository
     * @param Sameday $sameday
     */
    public function __construct(
        Sameday $sameday,
        SamedayPickupPointRepository $pickupPointRepository
    )
    {
        $this->sameday = $sameday;
        $this->samedayPickupPointRepository = $pickupPointRepository;
    }
}
