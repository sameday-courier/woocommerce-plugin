<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use Sameday\Sameday;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\ParcelStatusHistoryService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPackageRepository;

final class ShowHistoryAwbRequest
{
    private ShowHistoryAwbItem $showHistoryAwbItem;

    private SamedayAwbRepository $samedayAwbRepository;

    private SamedayPackageRepository $samedayPackageRepository;

    private Sameday $sameday;

    private ParcelStatusHistoryService $parcelStatusHistoryService;

    public function __construct(
        ShowHistoryAwbItem $showHistoryAwbItem,
        SamedayAwbRepository $samedayAwbRepository,
        SamedayPackageRepository $samedayPackageRepository,
        Sameday $sameday,
        ?ParcelStatusHistoryService $parcelStatusHistoryService = null
    ) {
        $this->showHistoryAwbItem = $showHistoryAwbItem;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->samedayPackageRepository = $samedayPackageRepository;
        $this->sameday = $sameday;
        $this->parcelStatusHistoryService = $parcelStatusHistoryService ?? new ParcelStatusHistoryService();
    }

    public function getShowHistoryAwbItem(): ShowHistoryAwbItem
    {
        return $this->showHistoryAwbItem;
    }

    public function getSamedayAwbRepository(): SamedayAwbRepository
    {
        return $this->samedayAwbRepository;
    }

    public function getSamedayPackageRepository(): SamedayPackageRepository
    {
        return $this->samedayPackageRepository;
    }

    public function getSameday(): Sameday
    {
        return $this->sameday;
    }

    public function getParcelStatusHistoryService(): ParcelStatusHistoryService
    {
        return $this->parcelStatusHistoryService;
    }
}
