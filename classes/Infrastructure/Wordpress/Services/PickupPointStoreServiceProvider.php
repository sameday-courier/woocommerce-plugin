<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\CourierPickupPointDto;
use SamedayCourier\Shipping\Domain\Models\CarrierPickupPoint;
use SamedayCourier\Shipping\Domain\Ports\PickupPointStoreServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;

final class PickupPointStoreServiceProvider implements PickupPointStoreServiceProviderInterface
{
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    public function __construct(?SamedayPickupPointRepository $samedayPickupPointRepository = null)
    {
        $this->samedayPickupPointRepository = $samedayPickupPointRepository ?? new SamedayPickupPointRepository();
    }

    public function getBySamedayId(int $samedayId): ?CarrierPickupPoint
    {
        return $this->samedayPickupPointRepository->getPickupPointSameday($samedayId);
    }

    /**
     * @return CarrierPickupPoint[]
     */
    public function getAll(): array
    {
        return $this->samedayPickupPointRepository->getPickupPoints();
    }

    public function add(CourierPickupPointDto $pickupPoint): void
    {
        $this->samedayPickupPointRepository->addPickupPoint($pickupPoint);
    }

    public function updateFromRemote(CourierPickupPointDto $pickupPoint, int $localId): bool
    {
        return $this->samedayPickupPointRepository->updatePickupPoint($pickupPoint, $localId);
    }

    public function deleteById(int $id): void
    {
        $this->samedayPickupPointRepository->deletePickupPoint($id);
    }
}
