<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use Sameday\Sameday;

final class AddNewPickupPointRequest
{
    /**
     * @var AddNewPickupPointItem $addNewPickupPointItem
     */
    private AddNewPickupPointItem $addNewPickupPointItem;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    public function __construct(
        AddNewPickupPointItem $addNewPickupPointItem,
        Sameday $sameday
    )
    {
        $this->addNewPickupPointItem = $addNewPickupPointItem;
        $this->sameday = $sameday;
    }

    /**
     * @return AddNewPickupPointItem
     */
    public function getAddNewPickupPointItem(): AddNewPickupPointItem
    {
        return $this->addNewPickupPointItem;
    }

    /**
     * @return Sameday
     */
    public function getSameday(): Sameday
    {
        return $this->sameday;
    }
}
