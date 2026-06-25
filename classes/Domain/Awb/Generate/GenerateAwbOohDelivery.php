<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate;

if (!defined('ABSPATH')) {
    exit;
}

final class AwbOohDelivery
{
    /**
     * @var int|string|null
     */
    private $lockerId;

    /**
     * @var int|string|null
     */
    private $oohLastMile;

    /**
     * @param int|string|null $lockerId
     * @param int|string|null $oohLastMile
     */
    public function __construct($lockerId = null, $oohLastMile = null)
    {
        $this->lockerId = $lockerId;
        $this->oohLastMile = $oohLastMile;
    }

    /**
     * @return int|string|null
     */
    public function getLockerId()
    {
        return $this->lockerId;
    }

    /**
     * @return int|string|null
     */
    public function getOohLastMile()
    {
        return $this->oohLastMile;
    }
}
