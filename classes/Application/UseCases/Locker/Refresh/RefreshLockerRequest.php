<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;

if (!defined('ABSPATH')) {
    exit;
}

class RefreshLockerRequest
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    public SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var Sameday $sameday
     */
    public Sameday $sameday;

    /**
     * @param SamedayLockerRepository $samedayLockerRepository
     * @param Sameday $sameday
     */
    public function __construct(
        SamedayLockerRepository $samedayLockerRepository,
        Sameday $sameday
    )
    {
        $this->samedayLockerRepository = $samedayLockerRepository;
        $this->sameday = $sameday;
    }
}
