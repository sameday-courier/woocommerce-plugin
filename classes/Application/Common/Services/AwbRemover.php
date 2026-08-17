<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Services;

use SamedayCourier\Shipping\Domain\DTOs\Requests\PostRemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\AwbNotFoundForOrderException;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierAwb;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostRemoveAwbServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\RemoveAwbServiceProviderInterface;

final class AwbRemover
{
    /**
     * @var OrderAwbProviderInterface $orderAwbProvider
     */
    private OrderAwbProviderInterface $orderAwbProvider;

    /**
     * @var RemoveAwbServiceProviderInterface $removeAwbServiceProvider
     */
    private RemoveAwbServiceProviderInterface $removeAwbServiceProvider;

    /**
     * @var PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider
     */
    private PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider;

    public function __construct(
        OrderAwbProviderInterface $orderAwbProvider,
        RemoveAwbServiceProviderInterface $removeAwbServiceProvider,
        PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider
    ) {
        $this->orderAwbProvider = $orderAwbProvider;
        $this->removeAwbServiceProvider = $removeAwbServiceProvider;
        $this->postRemoveAwbServiceProvider = $postRemoveAwbServiceProvider;
    }

    /**
     * @param int $orderId
     *
     * @return void
     *
     * @throws AwbNotFoundForOrderException
     * @throws CourierServiceException
     */
    public function remove(int $orderId): void
    {
        $awb = $this->orderAwbProvider->get($orderId);
        if (null === $awb) {
            throw new AwbNotFoundForOrderException($orderId);
        }

        $this->removeRemote((string) $awb->getAwbNumber());
        $this->removeLocal($awb);
    }

    /**
     * @param string $awbNumber
     *
     * @return void
     *
     * @throws CourierServiceException
     */
    public function removeRemote(string $awbNumber): void
    {
        $this->removeAwbServiceProvider->remove(new RemoveAwbRequestDto($awbNumber));
    }

    /**
     * Removes the local persistence of the AWB and its parcels.
     *
     * @param CarrierAwb $awb
     *
     * @return void
     */
    private function removeLocal(CarrierAwb $awb): void
    {
        $this->postRemoveAwbServiceProvider->apply(new PostRemoveAwbRequestDto($awb));
    }
}
