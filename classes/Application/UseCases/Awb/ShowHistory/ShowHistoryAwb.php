<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayGetParcelStatusHistoryRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPackageRepository;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;

if (!defined('ABSPATH')) {
    exit;
}

class ShowHistoryAwb
{
    private ShowHistoryAwbRequest $showHistoryAwbRequest;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var SamedayPackageRepository $samedayPackageRepository
     */
    private SamedayPackageRepository $samedayPackageRepository;

    public function __construct(ShowHistoryAwbRequest $showHistoryAwbRequest)
    {
        $this->showHistoryAwbRequest = $showHistoryAwbRequest;
        $this->samedayAwbRepository = new SamedayAwbRepository();
        $this->samedayPackageRepository = new SamedayPackageRepository();
    }

    /**
     * @return ShowHistoryAwbResponse
     *
     * @throws SamedaySDKException
     */
    public function execute(): ShowHistoryAwbResponse
    {
        $orderId = $this->showHistoryAwbRequest->getOrderId();
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb) {
            return new ShowHistoryAwbResponse(
                $orderId,
                false,
                [],
            );
        }

        $sameday = new Sameday(SdkInitiator::init());
        $parcels = unserialize($awb->getParcels() ?? '', ['']);

        $this->samedayPackageRepository->deletePackagesByOrderId($orderId);

        foreach ($parcels as $parcel) {
            try {
                $parcelStatus = $sameday->getParcelStatusHistory(
                    new SamedayGetParcelStatusHistoryRequest($parcel->getAwbNumber())
                );
            } catch (Exception $exception) {
                return new ShowHistoryAwbResponse(
                    $orderId,
                    true,
                    [],
                );
            }

            $this->samedayPackageRepository->refreshPackageHistory(
                $orderId,
                $parcel->getAwbNumber(),
                $parcelStatus->getSummary(),
                $parcelStatus->getHistory(),
                $parcelStatus->getExpeditionStatus()
            );
        }

        return new ShowHistoryAwbResponse(
            $orderId,
            true,
            $this->samedayPackageRepository->getPackagesForOrderId($orderId),
        );
    }
}
