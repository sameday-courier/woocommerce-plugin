<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use Sameday\Sameday;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshPickupPointRequest
{
    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @param Sameday $sameday
     * @param SamedayPickupPointRepository $pickupPointRepository
     */
    public function __construct(
        Sameday $sameday,
        SamedayPickupPointRepository $pickupPointRepository
    )
    {
        $this->sameday = $sameday;
        $this->samedayPickupPointRepository = $pickupPointRepository;
    }

    /**
     * @return Sameday
     */
    public function getSameday(): Sameday
    {
        return $this->sameday;
    }

    /**
     * @return SamedayPickupPointRepository
     */
    public function getSamedayPickupPointRepository(): SamedayPickupPointRepository
    {
        return $this->samedayPickupPointRepository;
    }
}
