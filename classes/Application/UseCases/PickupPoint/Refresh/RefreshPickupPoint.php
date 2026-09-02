<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

use SamedayCourier\Shipping\Domain\DTOs\Requests\GetPickupPointsRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierPickupPoint;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PickupPointStoreServiceProviderInterface;

/**
 * @extends AbstractUseCase<RefreshPickupPointRequest, RefreshPickupPointResponse>
 *
 * @method RefreshPickupPointResponse execute(RefreshPickupPointRequest $request)
 */
final class RefreshPickupPoint extends AbstractUseCase
{
    /**
     * @var CourierServiceProviderInterface $courierServiceProvider
     */
    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @var PickupPointStoreServiceProviderInterface $pickupPointStore
     */
    private PickupPointStoreServiceProviderInterface $pickupPointStore;

    /**
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param PickupPointStoreServiceProviderInterface $pickupPointStore
     */
    public function __construct(
        CourierServiceProviderInterface $courierServiceProvider,
        PickupPointStoreServiceProviderInterface $pickupPointStore
    ) {
        $this->courierServiceProvider = $courierServiceProvider;
        $this->pickupPointStore = $pickupPointStore;
    }

    /**
     * @param RefreshPickupPointRequest $request
     * @return RefreshPickupPointResponse
     */
    protected function processAction(RequestInterface $request): RefreshPickupPointResponse
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
                    true
                );
            }

            foreach ($pickUpPoints->getPickupPoints() as $pickupPointDto) {
                $pickupPoint = $this->pickupPointStore->getBySamedayId($pickupPointDto->getId());
                if (null === $pickupPoint) {
                    $this->pickupPointStore->add($pickupPointDto);
                } elseif (!$this->pickupPointStore->updateFromRemote($pickupPointDto, $pickupPoint->getId())) {
                    return new RefreshPickupPointResponse(
                        'Unable to update pickup point',
                        true
                    );
                }

                $remotePickupPoints[] = $pickupPointDto->getId();
            }
        } while ($page <= $pickUpPoints->getPages());

        $localPickupPoints = array_map(
            /**
             * @param CarrierPickupPoint $pickupPoint
             *
             * @return array
             */
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
            false
        );
    }
}
