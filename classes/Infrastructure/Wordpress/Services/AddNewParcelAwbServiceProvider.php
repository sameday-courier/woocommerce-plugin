<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\PostAwb\ParcelObject;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Domain\DTOs\Requests\AddNewParcelAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostParcelRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\AddNewParcelAwbResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\AddNewParcelAwbServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class AddNewParcelAwbServiceProvider implements AddNewParcelAwbServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    private SamedayAwbRepository $samedayAwbRepository;

    private ParcelDimensionsFactory $parcelDimensionsFactory;

    public function __construct(
        ?CourierServiceProviderInterface $courier = null,
        ?SamedayAwbRepository $samedayAwbRepository = null,
        ?ParcelDimensionsFactory $parcelDimensionsFactory = null
    ) {
        $this->courier = $courier ?? new CourierServiceProvider();
        $this->samedayAwbRepository = $samedayAwbRepository ?? new SamedayAwbRepository();
        $this->parcelDimensionsFactory = $parcelDimensionsFactory ?? new ParcelDimensionsFactory();
    }

    /**
     * @param AddNewParcelAwbRequestDto $addNewParcelAwbRequestDto
     *
     * @return AddNewParcelAwbResponseDto
     */
    public function add(AddNewParcelAwbRequestDto $addNewParcelAwbRequestDto): AddNewParcelAwbResponseDto
    {
        $orderId = $addNewParcelAwbRequestDto->getOrderId();
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb) {
            return new AddNewParcelAwbResponseDto(
                $orderId,
                false,
                'AWB not found for this order.'
            );
        }

        $position = $this->getPosition($awb->getParcels() ?? '');

        $parcelDimensionsObject = $this->parcelDimensionsFactory->fromAttributes(
            $addNewParcelAwbRequestDto->getParcelWeight(),
            $addNewParcelAwbRequestDto->getParcelWidth(),
            $addNewParcelAwbRequestDto->getParcelLength(),
            $addNewParcelAwbRequestDto->getParcelHeight(),
        );

        try {
            $parcel = $this->courier->postParcel(
                new PostParcelRequestDto(
                    (string) $awb->getAwbNumber(),
                    $parcelDimensionsObject,
                    $position,
                    $addNewParcelAwbRequestDto->getParcelObservation(),
                    null,
                    $addNewParcelAwbRequestDto->isParcelIsLast()
                )
            );
        } catch (CourierServiceException $exception) {
            return new AddNewParcelAwbResponseDto(
                $orderId,
                false,
                $exception->getMessage()
            );
        }

        $parcels = array_merge(
            $this->unserializeParcels($awb->getParcels() ?? ''),
            [
                new ParcelObject(
                    $position,
                    $parcel->getParcelAwbNumber()
                ),
            ]
        );

        if (!$this->samedayAwbRepository->updateParcels($awb->getOrderId(), serialize($parcels))) {
            return new AddNewParcelAwbResponseDto(
                $orderId,
                false,
                'Unable to update AWB parcels'
            );
        }

        return new AddNewParcelAwbResponseDto(
            $orderId,
            true,
            'AWB added new parcel successfully.'
        );
    }

    /**
     * @param string $parcels
     *
     * @return int
     */
    private function getPosition(string $parcels): int
    {
        return count($this->unserializeParcels($parcels)) + 1;
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
