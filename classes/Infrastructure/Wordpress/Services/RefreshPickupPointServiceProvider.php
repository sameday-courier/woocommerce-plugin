<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\Requests\GetPickupPointsRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RefreshPickupPointRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RefreshPickupPointResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierPickupPoint;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\RefreshPickupPointServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;

final class RefreshPickupPointServiceProvider implements RefreshPickupPointServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    private SamedayPickupPointRepository $samedayPickupPointRepository;

    public function __construct(
        ?CourierServiceProviderInterface $courier = null,
        ?SamedayPickupPointRepository $samedayPickupPointRepository = null
    ) {
        $this->courier = $courier ?? new CourierServiceProvider();
        $this->samedayPickupPointRepository = $samedayPickupPointRepository ?? new SamedayPickupPointRepository();
    }

    /**
     * @param RefreshPickupPointRequestDto $refreshPickupPointRequestDto
     *
     * @return RefreshPickupPointResponseDto
     */
    public function refresh(RefreshPickupPointRequestDto $refreshPickupPointRequestDto): RefreshPickupPointResponseDto
    {
        $remotePickupPoints = [];
        $page = 1;

        do {
            try {
                $pickUpPoints = $this->courier->getPickupPoints(new GetPickupPointsRequestDto($page++));
            } catch (CourierServiceException $e) {
                return new RefreshPickupPointResponseDto(
                    false,
                    $e->getMessage()
                );
            }

            foreach ($pickUpPoints->getPickupPoints() as $pickupPointObject) {
                $pickupPoint = $this->samedayPickupPointRepository->getPickupPointSameday($pickupPointObject->getId());
                if (null === $pickupPoint) {
                    $this->samedayPickupPointRepository->addPickupPoint($pickupPointObject);
                } elseif (!$this->samedayPickupPointRepository->updatePickupPoint($pickupPointObject, $pickupPoint->getId())) {
                    return new RefreshPickupPointResponseDto(
                        false,
                        'Unable to update pickup point'
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

        return new RefreshPickupPointResponseDto(
            true,
            "Pickup points successfully refreshed."
        );
    }
}
