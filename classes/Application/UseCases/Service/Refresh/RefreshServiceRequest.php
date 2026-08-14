<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Refresh;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class RefreshServiceRequest
{
    private CourierServiceProviderInterface $courier;

    /**
     * @var SamedayServiceRepository $samedayServiceRepository
     */
    private SamedayServiceRepository $samedayServiceRepository;

    /**
     * @param CourierServiceProviderInterface $courier
     * @param SamedayServiceRepository $samedayServiceRepository
     */
    public function __construct(
        CourierServiceProviderInterface $courier,
        SamedayServiceRepository $samedayServiceRepository
    )
    {
        $this->courier = $courier;
        $this->samedayServiceRepository = $samedayServiceRepository;
    }

    /**
     * @return CourierServiceProviderInterface
     */
    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }

    /**
     * @return SamedayServiceRepository
     */
    public function getSamedayServiceRepository(): SamedayServiceRepository
    {
        return $this->samedayServiceRepository;
    }
}
