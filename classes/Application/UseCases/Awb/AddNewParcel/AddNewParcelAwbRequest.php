<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

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
     * @param int $orderId
     * @param AddNewParcelAwbItem $awbItem
     */
    public function __construct(
        int $orderId,
        AddNewParcelAwbItem $awbItem
    )
    {
        $this->orderId = $orderId;
        $this->awbItem = $awbItem;
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
}
