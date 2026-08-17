<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetPickupPointsRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierPickupPoint;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;

final class RefreshPickupPoint
{
    private CourierServiceProviderInterface $courier;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @param RefreshPickupPointRequest $refreshPickupPointRequest
     */
    public function __construct(RefreshPickupPointRequest $refreshPickupPointRequest)
    {
        $this->courier = $refreshPickupPointRequest->getCourier();
        $this->samedayPickupPointRepository = $refreshPickupPointRequest->getSamedayPickupPointRepository();
    }

    /**
     * @return RefreshPickupPointResponse
     */
    public function execute(): RefreshPickupPointResponse
    {
        $remotePickupPoints = [];
        $page = 1;

        do {
            try {
                $pickUpPoints = $this->courier->getPickupPoints(new GetPickupPointsRequestDto($page++));
            } catch (CourierServiceException $e) {

                return new RefreshPickupPointResponse(
                    $e->getMessage(),
                    ResponseNoticeType::ERROR,
                );
            }

            foreach ($pickUpPoints->getPickupPoints() as $pickupPointObject) {
                $pickupPoint = $this->samedayPickupPointRepository->getPickupPointSameday($pickupPointObject->getId());
                if (null === $pickupPoint) {
                    $this->samedayPickupPointRepository->addPickupPoint($pickupPointObject);
                } elseif (!$this->samedayPickupPointRepository->updatePickupPoint($pickupPointObject, $pickupPoint->getId())) {
                    return new RefreshPickupPointResponse(
                        'Unable to update pickup point',
                        ResponseNoticeType::ERROR,
                    );
                }

                $remotePickupPoints[] = $pickupPointObject->getId();
            }
        } while ($page <= $pickUpPoints->getPages());

        $localPickupPoints = array_map(
            static function (CarrierPickupPoint $pickupPoint) {
                return [
                    'id' => $pickupPoint->getId(),
                    'sameday_id' => $pickupPoint->getSamedayId(),
                ];
            },
            $this->samedayPickupPointRepository->getPickupPoints()
        );

        foreach ($localPickupPoints as $localPickupPoint) {
            if (!in_array($localPickupPoint['sameday_id'], $remotePickupPoints, true)) {
                $this->samedayPickupPointRepository->deletePickupPoint((int) $localPickupPoint['id']);
            }
        }

        return new RefreshPickupPointResponse(
            "Pickup points successfully refreshed.",
            ResponseNoticeType::SUCCESS,
        );
    }
}
