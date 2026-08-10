<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\County\Get;

use Sameday\Sameday;

if (!defined('ABSPATH')) {
    exit;
}

final class GetCountiesRequest
{
    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    public function __construct(Sameday $sameday)
    {
        $this->sameday = $sameday;
    }

    /**
     * @return Sameday
     */
    public function getSameday(): Sameday
    {
        return $this->sameday;
    }
}
