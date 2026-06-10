<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Refresh;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshServiceRequest
{
    /**
     * @var Sameday $sameday
     */
    public Sameday $sameday;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    public SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param Sameday $sameday
     *
     * @param SamedayServiceRepository $samedayServiceRepository
     */
    public function __construct(
        Sameday $sameday,
        SamedayServiceRepository $samedayServiceRepository
    )
    {
        $this->sameday = $sameday;
        $this->samedayServiceRepository = $samedayServiceRepository;
    }
}
