<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class ShowAsPdfAwbRequest
{
    private ShowAsPdfAwbItem $showAsPdfAwbItem;

    private string $labelFormat;

    private SamedayAwbRepository $samedayAwbRepository;

    private CourierServiceProviderInterface $courier;

    public function __construct(
        ShowAsPdfAwbItem $showAsPdfAwbItem,
        string $labelFormat,
        SamedayAwbRepository $samedayAwbRepository,
        CourierServiceProviderInterface $courier
    ) {
        $this->showAsPdfAwbItem = $showAsPdfAwbItem;
        $this->labelFormat = $labelFormat;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->courier = $courier;
    }

    public function getShowAsPdfAwbItem(): ShowAsPdfAwbItem
    {
        return $this->showAsPdfAwbItem;
    }

    public function getOrderId(): int
    {
        return $this->showAsPdfAwbItem->getOrderId();
    }

    public function getLabelFormat(): string
    {
        return $this->labelFormat;
    }

    public function getSamedayAwbRepository(): SamedayAwbRepository
    {
        return $this->samedayAwbRepository;
    }

    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }
}
