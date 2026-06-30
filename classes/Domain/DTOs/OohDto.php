<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class OohDto
{
    /**
     * @var string|null $lockerId
     */
    public string $lockerId;

    /**
     * @var string|null $oohLastMile
     */
    public string $oohLastMile;

    /**
     * @param string $lockerId
     * @param string $oohLastMile
     */
    public function __construct(
        string $lockerId,
        string $oohLastMile
    )
    {
        $this->lockerId = $lockerId;
        $this->oohLastMile = $oohLastMile;
    }

    /**
     * @return string
     */
    public function getLockerId(): string
    {
        return $this->lockerId;
    }

    /**
     * @return string
     */
    public function getOohLastMile(): string
    {
        return $this->oohLastMile;
    }
}
