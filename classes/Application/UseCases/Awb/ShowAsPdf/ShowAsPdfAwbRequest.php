<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use Sameday\Sameday;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class ShowAsPdfAwbRequest
{
    private ShowAsPdfAwbItem $showAsPdfAwbItem;

    private string $labelFormat;

    private SamedayAwbRepository $samedayAwbRepository;

    private Sameday $sameday;

    public function __construct(
        ShowAsPdfAwbItem $showAsPdfAwbItem,
        string $labelFormat,
        SamedayAwbRepository $samedayAwbRepository,
        Sameday $sameday
    ) {
        $this->showAsPdfAwbItem = $showAsPdfAwbItem;
        $this->labelFormat = $labelFormat;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->sameday = $sameday;
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

    public function getSameday(): Sameday
    {
        return $this->sameday;
    }
}
