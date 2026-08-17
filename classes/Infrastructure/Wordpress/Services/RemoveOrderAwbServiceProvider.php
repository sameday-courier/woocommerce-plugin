<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\Requests\PostRemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveOrderAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RemoveOrderAwbResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostRemoveAwbServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\RemoveAwbServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\RemoveOrderAwbServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderAwbProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class RemoveOrderAwbServiceProvider implements RemoveOrderAwbServiceProviderInterface
{
    private OrderAwbProviderInterface $orderAwbProvider;

    private RemoveAwbServiceProviderInterface $removeAwbServiceProvider;

    private PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider;

    public function __construct(
        ?OrderAwbProviderInterface $orderAwbProvider = null,
        ?RemoveAwbServiceProviderInterface $removeAwbServiceProvider = null,
        ?PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider = null
    ) {
        $samedayAwbRepository = new SamedayAwbRepository();

        $this->orderAwbProvider = $orderAwbProvider ?? new WooOrderAwbProvider($samedayAwbRepository);
        $this->removeAwbServiceProvider = $removeAwbServiceProvider ?? new RemoveAwbServiceProvider();
        $this->postRemoveAwbServiceProvider = $postRemoveAwbServiceProvider
            ?? new PostRemoveAwbServiceProvider($samedayAwbRepository);
    }

    /**
     * @param RemoveOrderAwbRequestDto $removeOrderAwbRequestDto
     *
     * @return RemoveOrderAwbResponseDto
     */
    public function remove(RemoveOrderAwbRequestDto $removeOrderAwbRequestDto): RemoveOrderAwbResponseDto
    {
        $orderId = $removeOrderAwbRequestDto->getOrderId();
        $awb = $this->orderAwbProvider->get($orderId);

        if (null === $awb) {
            return new RemoveOrderAwbResponseDto(
                false,
                "Invalid or inexistent an AWB for this OrderID: {$orderId}"
            );
        }

        try {
            $this->removeAwbServiceProvider->remove(
                new RemoveAwbRequestDto((string) $awb->getAwbNumber())
            );
        } catch (CourierServiceException $exception) {
            return new RemoveOrderAwbResponseDto(
                false,
                $exception->getMessage()
            );
        }

        $this->postRemoveAwbServiceProvider->apply(new PostRemoveAwbRequestDto($awb));

        return new RemoveOrderAwbResponseDto(
            true,
            'Awb removed with success.'
        );
    }
}
