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
    public ?string $lockerId;

    /**
     * @var string|null $oohLastMile
     */
    public ?string $oohLastMile;

    /**
     * @param string|null $lockerId
     * @param string|null $oohLastMile
     */
    public function __construct(
        ?string $lockerId = null,
        ?string $oohLastMile = null
    )
    {
        $this->lockerId = $lockerId;
        $this->oohLastMile = $oohLastMile;
    }

    /**
     * @return null|string
     */
    public function getLockerId(): ?string
    {
        return $this->lockerId;
    }

    /**
     * @return null|string
     */
    public function getOohLastMile(): ?string
    {
        return $this->oohLastMile;
    }
}
