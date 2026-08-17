<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Domain\Ports\AddNewParcelAwbServiceProviderInterface;

final class AddNewParcelAwbRequest
{
    /**
     * @var AddNewParcelAwbItem $awbItem
     */
    private AddNewParcelAwbItem $awbItem;

    /**
     * @var AddNewParcelAwbServiceProviderInterface $addNewParcelAwbServiceProvider
     */
    private AddNewParcelAwbServiceProviderInterface $addNewParcelAwbServiceProvider;

    /**
     * @param AddNewParcelAwbItem $awbItem
     * @param AddNewParcelAwbServiceProviderInterface $addNewParcelAwbServiceProvider
     */
    public function __construct(
        AddNewParcelAwbItem $awbItem,
        AddNewParcelAwbServiceProviderInterface $addNewParcelAwbServiceProvider
    ) {
        $this->awbItem = $awbItem;
        $this->addNewParcelAwbServiceProvider = $addNewParcelAwbServiceProvider;
    }

    /**
     * @return AddNewParcelAwbItem
     */
    public function getAwbItem(): AddNewParcelAwbItem
    {
        return $this->awbItem;
    }

    /**
     * @return AddNewParcelAwbServiceProviderInterface
     */
    public function getAddNewParcelAwbServiceProvider(): AddNewParcelAwbServiceProviderInterface
    {
        return $this->addNewParcelAwbServiceProvider;
    }
}
