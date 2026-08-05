<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Application\UseCases\Awb\Common\AwbErrorParser;

if (!defined('ABSPATH')) {
    exit;
}

final class AddNewParcelAwbRequest
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var AddNewParcelAwbItem $awbItem
     */
    private AddNewParcelAwbItem $awbItem;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    /**
     * @param int $orderId
     * @param AddNewParcelAwbItem $awbItem
     * @param AwbErrorParser $awbErrorParser
     */
    public function __construct(
        int $orderId,
        AddNewParcelAwbItem $awbItem,
        AwbErrorParser $awbErrorParser
    )
    {
        $this->orderId = $orderId;
        $this->awbItem = $awbItem;
        $this->awbErrorParser = $awbErrorParser;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return AddNewParcelAwbItem
     */
    public function getAwbItem(): AddNewParcelAwbItem
    {
        return $this->awbItem;
    }

    /**
     * @return AwbErrorParser
     */
    public function getAwbErrorParser(): AwbErrorParser
    {
        return $this->awbErrorParser;
    }
}
