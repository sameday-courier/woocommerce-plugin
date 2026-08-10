<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;

if (!defined('ABSPATH')) {
    exit;
}

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
     * @param AddNewParcelAwbItem $awbItem
     * @param Sameday $sameday
     * @param SamedayAwbRepository $samedayAwbRepository
     * @param AwbErrorParser $awbErrorParser
     */
    public function __construct(
        AddNewParcelAwbItem $awbItem,
        Sameday $sameday,
        SamedayAwbRepository $samedayAwbRepository,
        AwbErrorParser $awbErrorParser
    ) {
        $this->awbItem = $awbItem;
        $this->sameday = $sameday;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->awbErrorParser = $awbErrorParser;
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
}
