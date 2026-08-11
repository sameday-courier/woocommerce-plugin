<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayGetParcelStatusHistoryRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPackageRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class ShowHistoryAwb
{
    private ShowHistoryAwbItem $showHistoryAwbItem;

    private SamedayAwbRepository $samedayAwbRepository;

    private SamedayPackageRepository $samedayPackageRepository;

    private Sameday $sameday;

    public function __construct(ShowHistoryAwbRequest $showHistoryAwbRequest)
    {
        $this->showHistoryAwbItem = $showHistoryAwbRequest->getShowHistoryAwbItem();
        $this->samedayAwbRepository = $showHistoryAwbRequest->getSamedayAwbRepository();
        $this->samedayPackageRepository = $showHistoryAwbRequest->getSamedayPackageRepository();
        $this->sameday = $showHistoryAwbRequest->getSameday();
    }

    /**
     * @return ShowHistoryAwbResponse
     *
     * @throws SamedaySDKException
     */
    public function execute(): ShowHistoryAwbResponse
    {
        $orderId = $this->showHistoryAwbItem->getOrderId();
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb) {
            return new ShowHistoryAwbResponse(
                $orderId,
                false,
                [],
            );
        }

        $parcels = unserialize($awb->getParcels() ?? '', ['']);

        $this->samedayPackageRepository->deletePackagesByOrderId($orderId);

        foreach ($parcels as $parcel) {
            try {
                $parcelStatus = $this->sameday->getParcelStatusHistory(
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
