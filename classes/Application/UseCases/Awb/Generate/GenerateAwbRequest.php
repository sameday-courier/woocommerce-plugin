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
    private GenerateAwbItem $generateAwbItem;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var DbHandler $dbHandler
     */
    private DbHandler $dbHandler;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater
     */
    private OrderShippingAddressUpdaterInterface $orderShippingAddressUpdater;

    /**
     * @var AwbErrorParser $awbErrorParser
     */
    private AwbErrorParser $awbErrorParser;

    /**
     * @var SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser
     */
    private SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser;

    /**
     * @var StateCodeResolverInterface $stateCodeResolver
     */
    private StateCodeResolverInterface $stateCodeResolver;

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

    /**
     * @return GenerateAwbItem
     */
    public function getGenerateAwbItem(): GenerateAwbItem
    {
        return $this->generateAwbItem;
    }

    /**
     * @return Sameday
     */
    public function getSameday(): Sameday
    {
        return $this->sameday;
    }

    /**
     * @return DbHandler
     */
    public function getDbHandler(): DbHandler
    {
        return $this->dbHandler;
    }

    /**
     * @return SamedayServiceRepository
     */
    public function getSamedayServiceRepository(): SamedayServiceRepository
    {
        return $this->samedayServiceRepository;
    }

    /**
     * @return SamedayAwbRepository
     */
    public function getSamedayAwbRepository(): SamedayAwbRepository
    {
        return $this->samedayAwbRepository;
    }

    /**
     * @return OrderShippingAddressUpdaterInterface
     */
    public function getOrderShippingAddressUpdater(): OrderShippingAddressUpdaterInterface
    {
        return $this->orderShippingAddressUpdater;
    }

    /**
     * @return AwbErrorParser
     */
    public function getAwbErrorParser(): AwbErrorParser
    {
        return $this->awbErrorParser;
    }

    /**
     * @return SamedayShippingHdAddressParserInterface
     */
    public function getSamedayShippingHdAddressParser(): SamedayShippingHdAddressParserInterface
    {
        return $this->samedayShippingHdAddressParser;
    }

    /**
     * @return StateCodeResolverInterface
     */
    public function getStateCodeResolver(): StateCodeResolverInterface
    {
        return $this->stateCodeResolver;
    }
}
