<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;

final class RefreshPickupPointRequest
{
    private CourierServiceProviderInterface $courier;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @param CourierServiceProviderInterface $courier
     * @param SamedayPickupPointRepository $pickupPointRepository
     */
    public function __construct(
        CourierServiceProviderInterface $courier,
        SamedayPickupPointRepository $pickupPointRepository
    )
    {
        $this->courier = $courier;
        $this->samedayPickupPointRepository = $pickupPointRepository;
    }

    /**
     * @return CourierServiceProviderInterface
     */
    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }

    /**
     * @return SamedayPickupPointRepository
     */
    public function getSamedayPickupPointRepository(): SamedayPickupPointRepository
    {
        return $this->samedayPickupPointRepository;
    }
}
