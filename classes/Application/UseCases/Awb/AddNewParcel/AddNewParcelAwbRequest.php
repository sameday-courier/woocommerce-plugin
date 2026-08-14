<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class AddNewParcelAwbRequest
{
    private AddNewParcelAwbItem $awbItem;

    private CourierServiceProviderInterface $courier;

    private SamedayAwbRepository $samedayAwbRepository;

    private ParcelDimensionsFactory $parcelDimensionsFactory;

    public function __construct(
        AddNewParcelAwbItem $awbItem,
        CourierServiceProviderInterface $courier,
        SamedayAwbRepository $samedayAwbRepository,
        ParcelDimensionsFactory $parcelDimensionsFactory
    ) {
        $this->awbItem = $awbItem;
        $this->courier = $courier;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->parcelDimensionsFactory = $parcelDimensionsFactory;
    }

    public function getAwbItem(): AddNewParcelAwbItem
    {
        return $this->awbItem;
    }

    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }

    public function getSamedayAwbRepository(): SamedayAwbRepository
    {
        return $this->samedayAwbRepository;
    }

    public function getParcelDimensionsFactory(): ParcelDimensionsFactory
    {
        return $this->parcelDimensionsFactory;
    }
}
