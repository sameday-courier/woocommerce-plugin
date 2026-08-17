<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Domain\Ports\RemoveOrderAwbServiceProviderInterface;

final class RemoveAwbRequest
{
    private RemoveAwbItem $removeAwbItem;

    private RemoveOrderAwbServiceProviderInterface $removeOrderAwbServiceProvider;

    public function __construct(
        RemoveAwbItem $removeAwbItem,
        RemoveOrderAwbServiceProviderInterface $removeOrderAwbServiceProvider
    ) {
        $this->removeAwbItem = $removeAwbItem;
        $this->removeOrderAwbServiceProvider = $removeOrderAwbServiceProvider;
    }

    public function getRemoveAwbItem(): RemoveAwbItem
    {
        return $this->removeAwbItem;
    }

    public function getRemoveOrderAwbServiceProvider(): RemoveOrderAwbServiceProviderInterface
    {
        return $this->removeOrderAwbServiceProvider;
    }
}
