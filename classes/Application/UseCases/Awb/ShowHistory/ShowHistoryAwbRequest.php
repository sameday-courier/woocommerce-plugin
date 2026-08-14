<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPackageRepository;

final class ShowHistoryAwbRequest
{
    private ShowHistoryAwbItem $showHistoryAwbItem;

    private SamedayAwbRepository $samedayAwbRepository;

    private SamedayPackageRepository $samedayPackageRepository;

    private CourierServiceProviderInterface $courier;

    public function __construct(
        ShowHistoryAwbItem $showHistoryAwbItem,
        SamedayAwbRepository $samedayAwbRepository,
        SamedayPackageRepository $samedayPackageRepository,
        CourierServiceProviderInterface $courier
    ) {
        $this->showHistoryAwbItem = $showHistoryAwbItem;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->samedayPackageRepository = $samedayPackageRepository;
        $this->courier = $courier;
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

    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }
}
