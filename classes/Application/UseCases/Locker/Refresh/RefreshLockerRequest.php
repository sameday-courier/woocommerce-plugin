<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use Sameday\Sameday;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshLockerRequest
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

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

    /**
     * @return SamedayLockerRepository
     */
    public function getSamedayLockerRepository(): SamedayLockerRepository
    {
        return $this->samedayLockerRepository;
    }

    /**
     * @return Sameday
     */
    public function getSameday(): Sameday
    {
        return $this->sameday;
    }
}
