<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use RuntimeException;
use Sameday\Objects\PostAwb\ParcelObject;
use Sameday\Sameday;
use SamedayCourier\Shipping\Domain\Models\SamedayAwb;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\ParcelStatusHistoryService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPackageRepository;
use Throwable;

final class ShowHistoryAwb
{
    private ShowHistoryAwbItem $showHistoryAwbItem;

    private SamedayAwbRepository $samedayAwbRepository;

    private SamedayPackageRepository $samedayPackageRepository;

    private Sameday $sameday;

    private ParcelStatusHistoryService $parcelStatusHistoryService;

    public function __construct(ShowHistoryAwbRequest $showHistoryAwbRequest)
    {
        $this->showHistoryAwbItem = $showHistoryAwbRequest->getShowHistoryAwbItem();
        $this->samedayAwbRepository = $showHistoryAwbRequest->getSamedayAwbRepository();
        $this->samedayPackageRepository = $showHistoryAwbRequest->getSamedayPackageRepository();
        $this->sameday = $showHistoryAwbRequest->getSameday();
        $this->parcelStatusHistoryService = $showHistoryAwbRequest->getParcelStatusHistoryService();
    }

    public function execute(): ShowHistoryAwbResponse
    {
        $orderId = $this->showHistoryAwbItem->getOrderId();
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb) {
            return new ShowHistoryAwbResponse(
                $orderId,
                false,
                [],
            );
        }

        $parcelAwbNumbers = $this->resolveParcelAwbNumbers($awb);
        if ([] === $parcelAwbNumbers) {
            return new ShowHistoryAwbResponse(
                $orderId,
                true,
                $this->samedayPackageRepository->getPackagesForOrderId($orderId),
            );
        }

        $errors = [];
        $hasRefreshedPackages = false;

        foreach ($parcelAwbNumbers as $parcelAwbNumber) {
            try {
                $parcelStatus = $this->parcelStatusHistoryService->get(
                    $this->sameday,
                    $parcelAwbNumber
                );

                if (!$hasRefreshedPackages) {
                    $this->samedayPackageRepository->deletePackagesByOrderId($orderId);
                    $hasRefreshedPackages = true;
                }

                $this->samedayPackageRepository->refreshPackageHistory(
                    $orderId,
                    $parcelAwbNumber,
                    $parcelStatus->getSummary(),
                    $parcelStatus->getHistory(),
                    $parcelStatus->getExpeditionStatus()
                );
            } catch (Throwable $exception) {
                $errors[] = sprintf('%s: %s', $parcelAwbNumber, $exception->getMessage());
            }
        }

        $packages = $this->samedayPackageRepository->getPackagesForOrderId($orderId);

        if ([] === $packages && [] !== $errors) {
            throw new RuntimeException(implode(' ', $errors));
        }

        return new ShowHistoryAwbResponse(
            $orderId,
            true,
            $packages,
        );
    }

    /**
     * @return string[]
     */
    private function resolveParcelAwbNumbers(SamedayAwb $awb): array
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

        $parcels = unserialize($serializedParcels, ['allowed_classes' => true]);
        if (!is_array($parcels)) {
            return [];
        }

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
}
