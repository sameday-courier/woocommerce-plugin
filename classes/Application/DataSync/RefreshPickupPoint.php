<?php

declare (strict_types = 1);

namespace SamedayCourier\Shipping\Application\DataSync;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayGetPickupPointsRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Domain\Models\SamedayPickupPoint;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;

if (!defined('ABSPATH')) {
    exit;
}

class RefreshPickupPoint
{
    /**
     * @var SamedayPickupPointRepository $samedayPickupPointRepository
     */
    private SamedayPickupPointRepository $samedayPickupPointRepository;

    public function __construct()
    {
        $this->samedayPickupPointRepository = new SamedayPickupPointRepository();
    }

    /**
     * @throws SamedaySDKException
     */
    public function refresh(): void
    {
        if (empty(OptionsHandler::getSamedayOptions())) {
            Redirector::to('admin.php', ['page' => 'sameday_pickup_points']);
        }

        $sameday = new Sameday(SdkInitiator::init());

        $remotePickupPoints = [];
        $page = 1;
        do {
            $request = new SamedayGetPickupPointsRequest();
            $request->setPage($page++);
            try {
                $pickUpPoints = $sameday->getPickupPoints($request);
            } catch (Exception $e) {
                Redirector::to('admin.php', ['page' => 'sameday_pickup_points']);
            }

            foreach ($pickUpPoints->getPickupPoints() as $pickupPointObject) {
                $pickupPoint = $this->samedayPickupPointRepository->getPickupPointSameday($pickupPointObject->getId());
                if (null === $pickupPoint) {
                    // Pickup point not found, add it.
                    $this->samedayPickupPointRepository->addPickupPoint($pickupPointObject);
                } else {
                    $this->samedayPickupPointRepository->updatePickupPoint($pickupPointObject, $pickupPoint->getId());
                }

                // Save as current pickup points.
                $remotePickupPoints[] = $pickupPointObject->getId();
            }
        } while ($page <= $pickUpPoints->getPages());

        // Build array of local pickup points.
        $localPickupPoints = array_map(
            static function (SamedayPickupPoint $pickupPoint) {
                return array(
                    'id' => $pickupPoint->getId(),
                    'sameday_id' => $pickupPoint->getSamedayId()
                );
            },

            $this->samedayPickupPointRepository->getPickupPoints()
        );

        // Delete local pickup points that aren't present in remote pickup points anymore.
        foreach ($localPickupPoints as $localPickupPoint) {
            if (!in_array($localPickupPoint['sameday_id'], $remotePickupPoints, true)) {
                $this->samedayPickupPointRepository->deletePickupPoint((int) $localPickupPoint['id']);
            }
        }

        Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_pickup_points']);
    }
}
