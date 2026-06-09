<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use Sameday\Sameday;

if (!defined('ABSPATH')) {
    exit;
}

class AddNewPickupPointRequest
{
    /**
     * @var AddNewPickupPointItem $pickupPointItem
     */
    public AddNewPickupPointItem $pickupPointItem;

    /**
     * @var Sameday $sameday
     */
    public Sameday $sameday;

    public function __construct(
        AddNewPickupPointItem $pickupPointItem,
        Sameday $sameday
    )
    {
        $this->pickupPointItem = $pickupPointItem;
        $this->sameday = $sameday;
    }
}
