<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\Services\AwbRemover;

final class RemoveAwbRequest
{
    /**
     * @var RemoveAwbItem $removeAwbItem
     */
    private RemoveAwbItem $removeAwbItem;

    /**
     * @var AwbRemover $awbRemover
     */
    private AwbRemover $awbRemover;

    public function __construct(
        RemoveAwbItem $removeAwbItem,
        AwbRemover $awbRemover
    ) {
        $this->removeAwbItem = $removeAwbItem;
        $this->awbRemover = $awbRemover;
    }

    /**
     * @return RemoveAwbItem
     */
    public function getRemoveAwbItem(): RemoveAwbItem
    {
        return $this->removeAwbItem;
    }

    /**
     * @return AwbRemover
     */
    public function getAwbRemover(): AwbRemover
    {
        return $this->awbRemover;
    }
}
