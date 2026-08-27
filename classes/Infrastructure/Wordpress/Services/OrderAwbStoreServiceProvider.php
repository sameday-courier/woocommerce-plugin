<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\PostAwb\ParcelObject;
use SamedayCourier\Shipping\Domain\Models\CarrierAwb;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use Throwable;

final class OrderAwbStoreServiceProvider implements OrderAwbStoreServiceProviderInterface
{
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @param ?SamedayAwbRepository $samedayAwbRepository
     */
    public function __construct(?SamedayAwbRepository $samedayAwbRepository = null)
    {
        $this->samedayAwbRepository = $samedayAwbRepository ?? new SamedayAwbRepository();
    }

    /**
     * Returns the AWB generated for the given order, or null when none exists.
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
     * @param int $orderId
     * @param string $awbNumber
     * @param float $awbCost
     * @param array<int, array{position: int, awbNumber: string}> $parcels
     *
     * @return bool
     */
    public function save(int $orderId, string $awbNumber, float $awbCost, array $parcels): bool
    {
        try {
            $parcelObjects = array_map(
                static function (array $parcel): ParcelObject {
                    return new ParcelObject(
                        (int) $parcel['position'],
                        (string) $parcel['awbNumber']
                    );
                },
                $parcels
            );

            $this->samedayAwbRepository->saveAwb([
                'order_id' => $orderId,
                'awb_number' => $awbNumber,
                'parcels' => serialize($parcelObjects),
                'awb_cost' => $awbCost,
            ]);
        } catch (Throwable $exception) {
            return false;
        }

        return true;
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
     * @param CarrierAwb $awb
     *
     * @return string[]
     */
    public function parcelAwbNumbers(CarrierAwb $awb): array
    {
        $parcelAwbNumbers = $this->extractParcelAwbNumbers($awb->getParcels());
        if ([] !== $parcelAwbNumbers) {
            return $parcelAwbNumbers;
        }

        $mainAwbNumber = trim((string) $awb->getAwbNumber());
        if ('' === $mainAwbNumber) {
            return [];
        }

        return [$mainAwbNumber];
    }

    /**
     * @param string|null $serializedParcels
     *
     * @return string[]
     */
    private function extractParcelAwbNumbers(?string $serializedParcels): array
    {
        if (null === $serializedParcels || '' === $serializedParcels) {
            return [];
        }

        $parcels = $this->unserializeParcels($serializedParcels);
        $parcelAwbNumbers = [];

        foreach ($parcels as $parcel) {
            if ($parcel instanceof ParcelObject) {
                $parcelAwbNumber = trim($parcel->getAwbNumber());
            } elseif (is_object($parcel) && method_exists($parcel, 'getAwbNumber')) {
                $parcelAwbNumber = trim((string) $parcel->getAwbNumber());
            } else {
                continue;
            }

            if ('' === $parcelAwbNumber) {
                continue;
            }

            $parcelAwbNumbers[] = $parcelAwbNumber;
        }

        return array_values(array_unique($parcelAwbNumbers));
    }

    /**
     * @param string $parcels
     *
     * @return array<int,
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
