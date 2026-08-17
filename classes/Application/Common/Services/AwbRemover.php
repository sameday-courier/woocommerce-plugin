<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Services;

use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\AwbNotFoundForOrderException;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierAwb;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class AwbRemover
{
    /**
     * @var CourierServiceProviderInterface $courier
     */
    private CourierServiceProviderInterface $courier;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    public function __construct(
        CourierServiceProviderInterface $courier,
        SamedayAwbRepository $samedayAwbRepository
    ) {
        $this->courier = $courier;
        $this->samedayAwbRepository = $samedayAwbRepository;
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
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);
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
        $this->courier->removeAwb(new RemoveAwbRequestDto($awbNumber));
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
        $this->samedayAwbRepository->deleteAwbAndParcels($awb);
    }
}
