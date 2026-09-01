<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class OohDto
{
    /**
     * @var int|null $lockerId
     */
    public ?int $lockerId;

    /**
     * @var int|null $oohLastMile
     */
    public ?int $oohLastMile;

    /**
     * @param int|null $lockerId
     * @param int|null $oohLastMile
     */
    public function __construct(
        ?int $lockerId = null,
        ?int $oohLastMile = null
    ) {
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
     * @return null|int
     */
    public function getOohLastMile(): ?int
    {
        return $this->oohLastMile;
    }
}
