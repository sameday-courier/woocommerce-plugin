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
use SamedayCourier\Shipping\Infrastructure\Woo\Admin\Views\AwbHistoryTable;

if (!defined('ABSPATH')) {
    exit;
}

class ShowHistoryAwb
{
    private ShowHistoryAwbRequest $showAwbHistoryAwbRequest;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @var SamedayPackageRepository $samedayPackageRepository
     */
    private SamedayPackageRepository $samedayPackageRepository;

    /**
     * @param ShowHistoryAwbRequest $showAwbHistoryAwbRequest
     */
    public function __construct(ShowHistoryAwbRequest $showAwbHistoryAwbRequest)
    {
        $this->showAwbHistoryAwbRequest = $showAwbHistoryAwbRequest;
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
        $sameday = new Sameday(SdkInitiator::init());
        $orderId = $this->showAwbHistoryAwbRequest->getOrderId();

        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);
        if (null === $awb) {
            return new ShowHistoryAwbResponse('');
        }

        $parcels = unserialize($awb->getParcels() ?? '', ['']);

        $this->samedayPackageRepository->deletePackagesByOrderId($orderId);

        foreach ($parcels as $parcel) {
            try {
                $parcelStatus = $sameday->getParcelStatusHistory(
                    new SamedayGetParcelStatusHistoryRequest($parcel->getAwbNumber())
                );
            } catch (Exception $exception) {
                return new ShowHistoryAwbResponse(
                    AwbHistoryTable::addAwbHistoryTable([])
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

        $packages = $this->samedayPackageRepository->getPackagesForOrderId($orderId);

        return new ShowHistoryAwbResponse(
            AwbHistoryTable::addAwbHistoryTable($packages)
        );
    }
}
