<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\Responses\GetParcelStatusHistoryResponseDto;
use SamedayCourier\Shipping\Domain\Ports\PackageHistoryStoreServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPackageRepository;

final class PackageHistoryStoreServiceProvider implements PackageHistoryStoreServiceProviderInterface
{
    private SamedayPackageRepository $samedayPackageRepository;

    /**
     * @param ?SamedayPackageRepository $samedayPackageRepository
     */
    public function __construct(?SamedayPackageRepository $samedayPackageRepository = null)
    {
        $this->samedayPackageRepository = $samedayPackageRepository ?? new SamedayPackageRepository();
    }

    /**
     * @param int $orderId
     *
     * @return array
     */
    public function getForOrder(int $orderId): array
    {
        return $this->samedayPackageRepository->getPackagesForOrderId($orderId);
    }

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function deleteByOrder(int $orderId): void
    {
        $this->samedayPackageRepository->deletePackagesByOrderId($orderId);
    }

    /**
     * @param int $orderId
     * @param string $parcelAwbNumber
     * @param GetParcelStatusHistoryResponseDto $parcelStatus
     *
     * @return void
     */
    public function refresh(
        int $orderId,
        string $parcelAwbNumber,
        GetParcelStatusHistoryResponseDto $parcelStatus
    ): void {
        $this->samedayPackageRepository->refreshPackageHistory(
            $orderId,
            $parcelAwbNumber,
            $parcelStatus->getSummary(),
            $parcelStatus->getHistory(),
            $parcelStatus->getExpeditionStatus()
        );
    }
}
