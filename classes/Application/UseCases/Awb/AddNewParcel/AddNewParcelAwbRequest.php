<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

if (!defined('ABSPATH')) {
    exit;
}

class AddNewParcelAwbRequest
{
    private int $orderId;
    private AddNewParcelAwbItem $awbItem;

    public function __construct(
        int $orderId,
        AddNewParcelAwbItem $awbItem
    )
    {
        $this->orderId = $orderId;
        $this->awbItem = $awbItem;
    }

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
}
