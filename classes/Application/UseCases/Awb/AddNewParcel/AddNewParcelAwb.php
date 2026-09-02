<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

use SamedayCourier\Shipping\Domain\DTOs\Requests\PostParcelRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;

/**
 * @extends AbstractUseCase<AddNewParcelAwbRequest, AddNewParcelAwbResponse>
 *
 * @method AddNewParcelAwbResponse execute(AddNewParcelAwbRequest $request)
 */
final class AddNewParcelAwb extends AbstractUseCase
{
    /**
     * @var OrderAwbStoreServiceProviderInterface $orderAwbStore
     */
    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    /**
     * @var CourierServiceProviderInterface $courierServiceProvider
     */
    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @param OrderAwbStoreServiceProviderInterface $orderAwbStore
     * @param CourierServiceProviderInterface $courierServiceProvider
     */
    public function __construct(
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider
    ) {
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
    }

    /**
     * @param AddNewParcelAwbRequest $request
     *
     * @return AddNewParcelAwbResponse
     */
    protected function processAction(RequestInterface $request): AddNewParcelAwbResponse
    {
        $orderId = $request->getOrderId();
        $awb = $this->orderAwbStore->getByOrderId($orderId);

        if (null === $awb) {
            return new AddNewParcelAwbResponse(
                'AWB not found for this order.',
                true,
                $orderId
            );
        }

        $position = $this->orderAwbStore->nextPosition($awb);

        try {
            $parcel = $this->courierServiceProvider->postParcel(
                new PostParcelRequestDto(
                    (string) $awb->getAwbNumber(),
                    $request->getParcelWeight(),
                    $request->getParcelWidth(),
                    $request->getParcelLength(),
                    $request->getParcelHeight(),
                    $position,
                    $request->getParcelObservation(),
                    null,
                    $request->isParcelIsLast()
                )
            );
        } catch (CourierServiceException $exception) {
            return new AddNewParcelAwbResponse(
                $exception->getMessage(),
                true,
                $orderId
            );
        }

        if (!$this->orderAwbStore->appendParcel($awb, $position, $parcel->getParcelAwbNumber())) {
            return new AddNewParcelAwbResponse(
                'Unable to update AWB parcels',
                true,
                $orderId
            );
        }

        return new AddNewParcelAwbResponse(
            'AWB added new parcel successfully.',
            false,
            $orderId
        );
    }
}
