<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbRequest
{
    /**
     * @var GenerateAwbItem $generateAwbItem
     */
    public GenerateAwbItem $generateAwbItem;

    /**
     * @var Sameday $sameday
     */
    public Sameday $sameday;

    /**
     * @var DbHandler $dbHandler
     */
    public DbHandler $dbHandler;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    public SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    public SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater
     */
    public WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater;

    /**
     * @param GenerateAwbItem $generateAwbItem
     * @param Sameday $sameday
     * @param DbHandler $dbHandler
     * @param SamedayServiceRepository $samedayServiceRepository
     * @param SamedayAwbRepository $samedayAwbRepository
     * @param WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater
     */
    public function __construct(
        GenerateAwbItem $generateAwbItem,
        Sameday $sameday,
        DbHandler $dbHandler,
        SamedayServiceRepository $samedayServiceRepository,
        SamedayAwbRepository $samedayAwbRepository,
        WooOrderShippingAddressUpdater $wooOrderShippingAddressUpdater
    ) {
        $this->generateAwbItem = $generateAwbItem;
        $this->sameday = $sameday;
        $this->dbHandler = $dbHandler;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->wooOrderShippingAddressUpdater = $wooOrderShippingAddressUpdater;
    }
}
