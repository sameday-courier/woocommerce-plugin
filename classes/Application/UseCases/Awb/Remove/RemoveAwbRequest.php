<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
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

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    public function __construct(
        RemoveAwbItem $removeAwbItem,
        AwbRemover $awbRemover,
        AwbErrorParser $awbErrorParser
    )
    {
        $this->removeAwbItem = $removeAwbItem;
        $this->awbRemover = $awbRemover;
        $this->awbErrorParser = $awbErrorParser;
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

    public function getAwbErrorParser(): AwbErrorParser
    {
        return $this->awbErrorParser;
    }
}
