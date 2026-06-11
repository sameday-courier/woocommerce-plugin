<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
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
     * @param GenerateAwbItem $generateAwbItem
     * @param DbHandler $dbHandler
     * @param SamedayServiceRepository $samedayServiceRepository
     * @param SamedayAwbRepository $samedayAwbRepository
     */
    public function __construct(
        GenerateAwbItem $generateAwbItem,
        DbHandler $dbHandler,
        SamedayServiceRepository $samedayServiceRepository,
        SamedayAwbRepository $samedayAwbRepository
    ) {
        $this->generateAwbItem = $generateAwbItem;
        $this->dbHandler = $dbHandler;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayAwbRepository = $samedayAwbRepository;
    }
}
