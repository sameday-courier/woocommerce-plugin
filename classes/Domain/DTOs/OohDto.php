<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class OohDto
{
    /**
     * @var int|null $lockerId
     */
    public ?int $lockerId;

    /**
     * @var string|null $oohLastMile
     */
    public ?string $oohLastMile;

    /**
     * @param int|null $lockerId
     * @param string|null $oohLastMile
     */
    public function __construct(
        ?int $lockerId = null,
        ?string $oohLastMile = null
    )
    {
        $this->lockerId = $lockerId;
        $this->oohLastMile = $oohLastMile;
    }

    /**
     * @return null|int
     */
    public function getLockerId(): ?int
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
