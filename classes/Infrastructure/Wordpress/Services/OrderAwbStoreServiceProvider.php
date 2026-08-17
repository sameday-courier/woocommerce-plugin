<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\PostAwb\ParcelObject;
use SamedayCourier\Shipping\Domain\Models\CarrierAwb;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class OrderAwbStoreServiceProvider implements OrderAwbStoreServiceProviderInterface
{
    private SamedayAwbRepository $samedayAwbRepository;

    public function __construct(?SamedayAwbRepository $samedayAwbRepository = null)
    {
        $this->samedayAwbRepository = $samedayAwbRepository ?? new SamedayAwbRepository();
    }

    /**
     * Returns the AWB generated for the given order, or null when none exists.
     *
     * The presence of a persisted AWB is the authoritative signal that a Sameday
     * AWB was generated for the order, independent of the shipping line method_id.
     *
     * @param int $orderId
     *
     * @return CarrierAwb|null
     */
    public function getByOrderId(int $orderId): ?CarrierAwb
    {
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb || null === $awb->getAwbNumber()) {
            return null;
        }

        return $awb;
    }

    /**
     * @param CarrierAwb $awb
     *
     * @return int
     */
    public function nextPosition(CarrierAwb $awb): int
    {
        return count($this->unserializeParcels($awb->getParcels() ?? '')) + 1;
    }

    /**
     * @param CarrierAwb $awb
     * @param int $position
     * @param string $parcelAwbNumber
     *
     * @return bool
     */
    public function appendParcel(CarrierAwb $awb, int $position, string $parcelAwbNumber): bool
    {
        $parcels = array_merge(
            $this->unserializeParcels($awb->getParcels() ?? ''),
            [
                new ParcelObject(
                    $position,
                    $parcelAwbNumber
                ),
            ]
        );

        return $this->samedayAwbRepository->updateParcels(
            $awb->getOrderId(),
            serialize($parcels)
        );
    }

    /**
     * @return array<int, mixed>
     */
    private function unserializeParcels(string $parcels): array
    {
        if ('' === $parcels) {
            return [];
        }

        $decoded = unserialize($parcels, ['allowed_classes' => true]);

        return is_array($decoded) ? $decoded : [];
    }
}
