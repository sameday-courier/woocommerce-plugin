<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPackageRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class ShowHistoryAwbRequest
{
    private ShowHistoryAwbItem $showHistoryAwbItem;

    private SamedayAwbRepository $samedayAwbRepository;

    private SamedayPackageRepository $samedayPackageRepository;

    private Sameday $sameday;

    public function __construct(
        ShowHistoryAwbItem $showHistoryAwbItem,
        SamedayAwbRepository $samedayAwbRepository,
        SamedayPackageRepository $samedayPackageRepository,
        Sameday $sameday
    ) {
        $this->showHistoryAwbItem = $showHistoryAwbItem;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->samedayPackageRepository = $samedayPackageRepository;
        $this->sameday = $sameday;
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
}
