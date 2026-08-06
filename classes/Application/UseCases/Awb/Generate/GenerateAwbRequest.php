<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Domain\Ports\OrderShippingAddressUpdaterInterface;
use SamedayCourier\Shipping\Domain\Ports\SamedayShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
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
     * @var OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater
     */
    public OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    public AwbErrorParser $awbErrorParser;

    /**
     * @var SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser
     */
    public SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser;

    /**
     * @var StateCodeResolverInterface $stateCodeResolver
     */
    public StateCodeResolverInterface $stateCodeResolver;

    /**
     * @param GenerateAwbItem $generateAwbItem
     * @param Sameday $sameday
     * @param DbHandler $dbHandler
     * @param SamedayServiceRepository $samedayServiceRepository
     * @param SamedayAwbRepository $samedayAwbRepository
     * @param OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater
     * @param AwbErrorParser $awbErrorParser
     * @param SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser
     * @param StateCodeResolverInterface $stateCodeResolver
     */
    public function __construct(
        GenerateAwbItem $generateAwbItem,
        Sameday $sameday,
        DbHandler $dbHandler,
        SamedayServiceRepository $samedayServiceRepository,
        SamedayAwbRepository $samedayAwbRepository,
        OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater,
        AwbErrorParser $awbErrorParser,
        SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser,
        StateCodeResolverInterface $stateCodeResolver
    ) {
        $this->generateAwbItem = $generateAwbItem;
        $this->sameday = $sameday;
        $this->dbHandler = $dbHandler;
        $this->samedayServiceRepository = $samedayServiceRepository;
        $this->samedayAwbRepository = $samedayAwbRepository;
        $this->orderShippingAddressUpdater = $orderShippingAddressUpdater;
        $this->awbErrorParser = $awbErrorParser;
        $this->samedayShippingHdAddressParser = $samedayShippingHdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
    }
}
