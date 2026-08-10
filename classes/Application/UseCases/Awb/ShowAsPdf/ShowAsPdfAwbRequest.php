<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

if (!defined('ABSPATH')) {
    exit;
}

final class ShowAsPdfAwbRequest
{
    /**
     * @var ShowAsPdfAwbItem $showAsPdfAwbItem
     */
    private ShowAsPdfAwbItem $showAsPdfAwbItem;

    /**
     * @var string $labelFormat
     */
    private string $labelFormat;

    /**
     * @param ShowAsPdfAwbItem $showAsPdfAwbItem
     * @param string $labelFormat
     */
    public function __construct(ShowAsPdfAwbItem $showAsPdfAwbItem, string $labelFormat)
    {
        $this->showAsPdfAwbItem = $showAsPdfAwbItem;
        $this->labelFormat = $labelFormat;
    }

    /**
     * @return ShowAsPdfAwbItem
     */
    public function getShowAsPdfAwbItem(): ShowAsPdfAwbItem
    {
        return $this->showAsPdfAwbItem;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->showAsPdfAwbItem->getOrderId();
    }

    /**
     * @return string
     */
    public function getLabelFormat(): string
    {
        return $this->labelFormat;
    }
}
