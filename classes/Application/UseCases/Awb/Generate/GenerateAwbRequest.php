<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Domain\Awb\Generate\GenerateAwbResolutionFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbRequest
{
    public GenerateAwbItem $generateAwbItem;

    public Sameday $sameday;

    public DbHandler $dbHandler;

    public SamedayServiceRepository $samedayServiceRepository;

    public SamedayAwbRepository $samedayAwbRepository;

    public GenerateAwbValidator $generateAwbValidator;

    public GenerateAwbResolutionFactory $generateAwbResolutionFactory;

    public function __construct(
        GenerateAwbItem $generateAwbItem,
        Sameday $sameday,
        DbHandler $dbHandler,
        SamedayServiceRepository $samedayServiceRepository,
        SamedayAwbRepository $samedayAwbRepository,
        GenerateAwbValidator $generateAwbValidator,
        GenerateAwbResolutionFactory $generateAwbResolutionFactory
    ) {
        $this->generateAwbItem = $generateAwbItem;
        $this->sameday = $sameday;
        $this->dbHandler = $dbHandler;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->generateAwbValidator = $generateAwbValidator;
        $this->generateAwbResolutionFactory = $generateAwbResolutionFactory;
    }
}
