<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Domain\Ports\AddNewParcelServiceProviderInterface;

final class AddNewParcelAwbRequest
{
    private AddNewParcelAwbItem $awbItem;

    private AddNewParcelServiceProviderInterface $addNewParcelServiceProvider;

    public function __construct(
        AddNewParcelAwbItem $awbItem,
        AddNewParcelServiceProviderInterface $addNewParcelServiceProvider
    ) {
        $this->awbItem = $awbItem;
        $this->addNewParcelServiceProvider = $addNewParcelServiceProvider;
    }

    public function getAwbItem(): AddNewParcelAwbItem
    {
        return $this->awbItem;
    }

    public function getAddNewParcelServiceProvider(): AddNewParcelServiceProviderInterface
    {
        return $this->addNewParcelServiceProvider;
    }
}
