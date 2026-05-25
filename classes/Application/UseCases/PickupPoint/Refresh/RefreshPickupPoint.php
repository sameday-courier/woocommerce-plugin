<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use Exception;
use Sameday\Requests\SamedayGetPickupPointsRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\Models\SamedayPickupPoint;

if (!defined('ABSPATH')) {
    exit;
}

class RefreshPickupPoint
{
    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    /**
     * @param RefreshPickupPointRequest $refreshPickupPointRequest
     */
    public function __construct(RefreshPickupPointRequest $refreshPickupPointRequest)
    {
        $this->sameday = $refreshPickupPointRequest->sameday;
        $this->samedayPickupPointRepository = $refreshPickupPointRequest->samedayPickupPointRepository;
    }

    /**
     * @return RefreshPickupPointResponse
     */
    public function execute(): RefreshPickupPointResponse
    {
        $remotePickupPoints = [];
        $page = 1;

        do {
            $request = new SamedayGetPickupPointsRequest();
            $request->setPage($page++);

            try {
                $pickUpPoints = $this->sameday->getPickupPoints($request);
            } catch (Exception $e) {

                return new RefreshPickupPointResponse(
                    ResponseNoticeType::ERROR,
                    $e->getMessage(),
                );
            }

            foreach ($pickUpPoints->getPickupPoints() as $pickupPointObject) {
                $pickupPoint = $this->samedayPickupPointRepository->getPickupPointSameday($pickupPointObject->getId());
                if (null === $pickupPoint) {
                    $this->samedayPickupPointRepository->addPickupPoint($pickupPointObject);
                } else {
                    $this->samedayPickupPointRepository->updatePickupPoint($pickupPointObject, $pickupPoint->getId());
                }

                $remotePickupPoints[] = $pickupPointObject->getId();
            }
        } while ($page <= $pickUpPoints->getPages());

        $localPickupPoints = array_map(
            static function (SamedayPickupPoint $pickupPoint) {
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

        return new RefreshPickupPointResponse(ResponseNoticeType::SUCCESS);
    }
}
