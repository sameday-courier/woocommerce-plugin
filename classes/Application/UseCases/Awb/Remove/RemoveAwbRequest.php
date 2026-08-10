<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\Common\Services\AwbRemover;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class RemoveAwbRequest
{
    /**
     * @var RemoveAwbItem $removeAwbItem
     */
    private RemoveAwbItem $removeAwbItem;

    /**
     * @var AwbRemover $awbRemover
     */
    private AwbRemover $awbRemover;

    /**
     * @var SamedayAwbRepository $awbRepository
     */
    private SamedayAwbRepository $awbRepository;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    public function __construct(
        RemoveAwbItem $removeAwbItem,
        SamedayAwbRepository $awbRepository,
        AwbRemover $awbRemover,
        AwbErrorParser $awbErrorParser
    )
    {
        $this->removeAwbItem = $removeAwbItem;
        $this->awbRepository = $awbRepository;
        $this->awbRemover = $awbRemover;
        $this->awbErrorParser = $awbErrorParser;
    }

    /**
     * @return RemoveAwbItem
     */
    public function getRemoveAwbItem(): RemoveAwbItem
    {
        return $this->removeAwbItem;
    }

    /**
     * @return SamedayAwbRepository
     */
    public function getAwbRepository(): SamedayAwbRepository
    {
        return $this->awbRepository;
    }

    /**
     * @return AwbRemover
     */
    public function getAwbRemover(): AwbRemover
    {
        return $this->awbRemover;
    }

    public function getAwbErrorParser(): AwbErrorParser
    {
        return $this->awbErrorParser;
    }
}
