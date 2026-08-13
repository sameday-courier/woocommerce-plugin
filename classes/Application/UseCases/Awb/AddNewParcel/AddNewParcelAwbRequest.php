<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class AddNewParcelAwbRequest
{
    /**
     * @var AddNewParcelAwbItem $awbItem
     */
    private AddNewParcelAwbItem $awbItem;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    /**
     * @var ParcelDimensionsFactory $parcelDimensionsFactory
     */
    private ParcelDimensionsFactory $parcelDimensionsFactory;

    /**
     * @param AddNewParcelAwbItem $awbItem
     * @param Sameday $sameday
     * @param SamedayAwbRepository $samedayAwbRepository
     * @param AwbErrorParser $awbErrorParser
     * @param ParcelDimensionsFactory $parcelDimensionsFactory
     */
    public function __construct(
        AddNewParcelAwbItem $awbItem,
        Sameday $sameday,
        SamedayAwbRepository $samedayAwbRepository,
        AwbErrorParser $awbErrorParser,
        ParcelDimensionsFactory $parcelDimensionsFactory
    ) {
        $this->awbItem = $awbItem;
        $this->sameday = $sameday;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->awbErrorParser = $awbErrorParser;
        $this->parcelDimensionsFactory = $parcelDimensionsFactory;
    }

    /**
     * @return AddNewParcelAwbItem
     */
    public function getAwbItem(): AddNewParcelAwbItem
    {
        return $this->awbItem;
    }

    /**
     * @return Sameday
     */
    public function getSameday(): Sameday
    {
        return $this->sameday;
    }

    /**
     * @return SamedayAwbRepository
     */
    public function getSamedayAwbRepository(): SamedayAwbRepository
    {
        return $this->samedayAwbRepository;
    }

    /**
     * @return AwbErrorParser
     */
    public function getAwbErrorParser(): AwbErrorParser
    {
        return $this->awbErrorParser;
    }

    /**
     * @return ParcelDimensionsFactory
     */
    public function getParcelDimensionsFactory(): ParcelDimensionsFactory
    {
        return $this->parcelDimensionsFactory;
    }
}
