<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

final class DeletePickupPointItem implements ItemInterface
{
    /**
     * @var int $samedayId
     */
    private int $samedayId;

    /**
     * @param int $samedayId
     */
    public function __construct(int $samedayId)
    {
        $this->samedayId = $samedayId;
    }

    /**
     * @param array $inputParams
     *
     * @return self
     */
    /**
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        return new self(
            (int) ($inputParams['sameday_id'] ?? 0),
        );
    }

    /**
     * @return int
     */
    public function getSamedayId(): int
    {
        return $this->samedayId;
    }
}
