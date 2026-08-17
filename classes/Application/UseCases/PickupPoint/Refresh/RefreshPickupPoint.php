<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetPickupPointsRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierPickupPoint;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PickupPointStoreServiceProviderInterface;

final class RefreshPickupPoint
{
    private CourierServiceProviderInterface $courierServiceProvider;

    private PickupPointStoreServiceProviderInterface $pickupPointStore;

    public function __construct(RefreshPickupPointRequest $refreshPickupPointRequest)
    {
        $this->courierServiceProvider = $refreshPickupPointRequest->getCourierServiceProvider();
        $this->pickupPointStore = $refreshPickupPointRequest->getPickupPointStore();
    }

    public function execute(): RefreshPickupPointResponse
    {
        $remotePickupPoints = [];
        $page = 1;

        do {
            try {
                $pickUpPoints = $this->courierServiceProvider->getPickupPoints(
                    new GetPickupPointsRequestDto($page++)
                );
            } catch (CourierServiceException $exception) {
                return new RefreshPickupPointResponse(
                    $exception->getMessage(),
                    ResponseNoticeType::ERROR
                );
            }

            foreach ($pickUpPoints->getPickupPoints() as $pickupPointDto) {
                $pickupPoint = $this->pickupPointStore->getBySamedayId($pickupPointDto->getId());
                if (null === $pickupPoint) {
                    $this->pickupPointStore->add($pickupPointDto);
                } elseif (!$this->pickupPointStore->updateFromRemote($pickupPointDto, $pickupPoint->getId())) {
                    return new RefreshPickupPointResponse(
                        'Unable to update pickup point',
                        ResponseNoticeType::ERROR
                    );
                }

                $remotePickupPoints[] = $pickupPointDto->getId();
            }
        } while ($page <= $pickUpPoints->getPages());

        $localPickupPoints = array_map(
            static function (CarrierPickupPoint $pickupPoint): array {
                return [
                    'id' => $pickupPoint->getId(),
                    'sameday_id' => $pickupPoint->getSamedayId(),
                ];
            },
            $this->pickupPointStore->getAll()
        );

        foreach ($localPickupPoints as $localPickupPoint) {
            if (!in_array($localPickupPoint['sameday_id'], $remotePickupPoints, true)) {
                $this->pickupPointStore->deleteById((int) $localPickupPoint['id']);
            }
        }

        return new RefreshPickupPointResponse(
            'Pickup points successfully refreshed.',
            ResponseNoticeType::SUCCESS
        );
    }
}
