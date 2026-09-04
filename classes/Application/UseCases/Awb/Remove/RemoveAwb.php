<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostRemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostRemoveAwbServiceProviderInterface;

/**
 * @extends AbstractUseCase<RemoveAwbRequest, RemoveAwbResponse>
 *
 * @method RemoveAwbResponse execute(RemoveAwbRequest $request)
 */
final class RemoveAwb extends AbstractUseCase
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
     * @var PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider
     */
    private PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider;

    /**
     * @param OrderAwbStoreServiceProviderInterface $orderAwbStore
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider
     */
    public function __construct(
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider,
        PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider
    ) {
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
        $this->postRemoveAwbServiceProvider = $postRemoveAwbServiceProvider;
    }

    /**
     * @param RemoveAwbRequest $request
     *
     * @return RemoveAwbResponse
     */
    protected function processAction(RequestInterface $request): RemoveAwbResponse
    {
        $orderId = $request->getOrderId();
        $awb = $this->orderAwbStore->getByOrderId($orderId);

        if (null === $awb) {
            return new RemoveAwbResponse(
                "Invalid or inexistent an AWB for this OrderID: {$orderId}",
                true
            );
        }

        try {
            $remoteRemoveResult = $this->courierServiceProvider->removeAwb(
                new RemoveAwbRequestDto((string) $awb->getAwbNumber())
            );
        } catch (CourierServiceException $exception) {
            return new RemoveAwbResponse(
                $exception->getMessage(),
                true
            );
        }

        $message = $remoteRemoveResult->getMessage();

        $localRemoveResult = $this->postRemoveAwbServiceProvider->apply(
            new PostRemoveAwbRequestDto($awb)
        );

        if (!$localRemoveResult->isSuccess()) {
            $message .= ' but the local record could not be deleted from your store.'
                . ' Please remove the remaining AWB entry from this order manually.';
        }

        return new RemoveAwbResponse(
            $message,
            false
        );
    }
}
