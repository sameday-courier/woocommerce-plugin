<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use RuntimeException;
use Sameday\Objects\PostAwb\ParcelObject;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetParcelStatusHistoryRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowHistoryAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\ShowHistoryAwbResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierAwb;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\ShowHistoryAwbServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPackageRepository;

final class ShowHistoryAwbServiceProvider implements ShowHistoryAwbServiceProviderInterface
{
    private SamedayAwbRepository $samedayAwbRepository;

    private SamedayPackageRepository $samedayPackageRepository;

    private CourierServiceProviderInterface $courier;

    public function __construct(
        ?SamedayAwbRepository $samedayAwbRepository = null,
        ?SamedayPackageRepository $samedayPackageRepository = null,
        ?CourierServiceProviderInterface $courier = null
    ) {
        $this->samedayAwbRepository = $samedayAwbRepository ?? new SamedayAwbRepository();
        $this->samedayPackageRepository = $samedayPackageRepository ?? new SamedayPackageRepository();
        $this->courier = $courier ?? new CourierServiceProvider();
    }

    /**
     * @param ShowHistoryAwbRequestDto $showHistoryAwbRequestDto
     *
     * @return ShowHistoryAwbResponseDto
     */
    public function showHistory(ShowHistoryAwbRequestDto $showHistoryAwbRequestDto): ShowHistoryAwbResponseDto
    {
        $orderId = $showHistoryAwbRequestDto->getOrderId();
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb) {
            return new ShowHistoryAwbResponseDto(
                $orderId,
                false,
                [],
            );
        }

        $parcelAwbNumbers = $this->resolveParcelAwbNumbers($awb);
        if ([] === $parcelAwbNumbers) {
            return new ShowHistoryAwbResponseDto(
                $orderId,
                true,
                $this->samedayPackageRepository->getPackagesForOrderId($orderId),
            );
        }

        $errors = [];
        $hasRefreshedPackages = false;

        foreach ($parcelAwbNumbers as $parcelAwbNumber) {
            try {
                $parcelStatus = $this->courier->getParcelStatusHistory(
                    new GetParcelStatusHistoryRequestDto($parcelAwbNumber)
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
            } catch (CourierServiceException $exception) {
                $errors[] = sprintf('%s: %s', $parcelAwbNumber, $exception->getMessage());
            }
        }

        $packages = $this->samedayPackageRepository->getPackagesForOrderId($orderId);

        if ([] === $packages && [] !== $errors) {
            throw new RuntimeException(implode(' ', $errors));
        }

        return new ShowHistoryAwbResponseDto(
            $orderId,
            true,
            $packages,
            $errors,
        );
    }

    /**
     * @return string[]
     */
    private function resolveParcelAwbNumbers(CarrierAwb $awb): array
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
