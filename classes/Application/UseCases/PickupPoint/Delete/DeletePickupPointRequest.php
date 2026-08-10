<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete;

use Sameday\Sameday;

if (!defined('ABSPATH')) {
    exit;
}

final class DeletePickupPointRequest
{
    /**
     * @var DeletePickupPointItem $deletePickupPointItem
     */
    private DeletePickupPointItem $deletePickupPointItem;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    public function __construct(
        DeletePickupPointItem $deletePickupPointItem,
        Sameday $sameday
    )
    {
        $this->deletePickupPointItem = $deletePickupPointItem;
        $this->sameday = $sameday;
    }

    /**
     * @return DeletePickupPointItem
     */
    public function getDeletePickupPointItem(): DeletePickupPointItem
    {
        return $this->deletePickupPointItem;
    }

    /**
     * @return Sameday
     */
    public function getSameday(): Sameday
    {
        return $this->sameday;
    }
}
