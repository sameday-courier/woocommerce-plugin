<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use Sameday\Objects\PostAwb\ParcelObject;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostParcelRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class AddNewParcelAwb
{
    private AddNewParcelAwbRequest $addNewParcelAwbRequest;

    private CourierServiceProviderInterface $courier;

    private SamedayAwbRepository $samedayAwbRepository;

    private ParcelDimensionsFactory $parcelDimensionsFactory;

    public function __construct(AddNewParcelAwbRequest $addNewParcelAwbRequest)
    {
        $this->addNewParcelAwbRequest = $addNewParcelAwbRequest;
        $this->courier = $addNewParcelAwbRequest->getCourier();
        $this->samedayAwbRepository = $addNewParcelAwbRequest->getSamedayAwbRepository();
        $this->parcelDimensionsFactory = $addNewParcelAwbRequest->getParcelDimensionsFactory();
    }

    public function execute(): AddNewParcelAwbResponse
    {
        $parcelItem = $this->addNewParcelAwbRequest->getAwbItem();

        $orderId = $parcelItem->getOrderId();
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb) {
            return new AddNewParcelAwbResponse(
                $orderId,
                'AWB not found for this order.',
                ResponseNoticeType::ERROR,
            );
        }

        $position = $this->getPosition($awb->getParcels() ?? '');

        $parcelDimensionsObject = $this->parcelDimensionsFactory->fromAttributes(
            $parcelItem->getParcelWeight(),
            $parcelItem->getParcelWidth(),
            $parcelItem->getParcelLength(),
            $parcelItem->getParcelHeight(),
        );

        try {
            $parcel = $this->courier->postParcel(
                new PostParcelRequestDto(
                    (string) $awb->getAwbNumber(),
                    $parcelDimensionsObject,
                    $position,
                    $parcelItem->getParcelObservation(),
                    null,
                    $parcelItem->isParcelIsLast()
                )
            );
        } catch (CourierServiceException $exception) {
            return new AddNewParcelAwbResponse(
                $orderId,
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        $parcels = array_merge(
            unserialize($awb->getParcels() ?? '', ['']),
            [
                new ParcelObject(
                    $position,
                    $parcel->getParcelAwbNumber()
                )
            ]
        );

        if (!$this->samedayAwbRepository->updateParcels($awb->getOrderId(), serialize($parcels))) {
            return new AddNewParcelAwbResponse(
                $orderId,
                'Unable to update AWB parcels',
                ResponseNoticeType::ERROR,
            );
        }

        return new AddNewParcelAwbResponse(
            $orderId,
            "AWB added new parcel successfully.",
            ResponseNoticeType::SUCCESS,
        );
    }

    /**
     * @param $parcels
     *
     * @return int
     */
    private function getPosition($parcels): int
    {
        $nrOfParcels = count(unserialize($parcels, ['']));

        return $nrOfParcels + 1;
    }
}
