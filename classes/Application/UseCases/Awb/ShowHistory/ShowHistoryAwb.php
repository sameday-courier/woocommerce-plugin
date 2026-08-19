<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use RuntimeException;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetParcelStatusHistoryRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PackageHistoryStoreServiceProviderInterface;

final class ShowHistoryAwb
{
    private ShowHistoryAwbItem $showHistoryAwbItem;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    private PackageHistoryStoreServiceProviderInterface $packageHistoryStore;

    /**
     * @param ShowHistoryAwbRequest $showHistoryAwbRequest
     */
    public function __construct(ShowHistoryAwbRequest $showHistoryAwbRequest)
    {
        $this->showHistoryAwbItem = $showHistoryAwbRequest->getShowHistoryAwbItem();
        $this->orderAwbStore = $showHistoryAwbRequest->getOrderAwbStore();
        $this->courierServiceProvider = $showHistoryAwbRequest->getCourierServiceProvider();
        $this->packageHistoryStore = $showHistoryAwbRequest->getPackageHistoryStore();
    }

    /**
     * @return ShowHistoryAwbResponse
     */
    public function execute(): ShowHistoryAwbResponse
    {
        $orderId = $this->showHistoryAwbItem->getOrderId();
        $awb = $this->orderAwbStore->getByOrderId($orderId);

        if (null === $awb) {
            return new ShowHistoryAwbResponse(
                $orderId,
                false,
                []
            );
        }

        $parcelAwbNumbers = $this->orderAwbStore->parcelAwbNumbers($awb);
        if ([] === $parcelAwbNumbers) {
            return new ShowHistoryAwbResponse(
                $orderId,
                true,
                $this->packageHistoryStore->getForOrder($orderId)
            );
        }

        $errors = [];
        $hasRefreshedPackages = false;

        foreach ($parcelAwbNumbers as $parcelAwbNumber) {
            try {
                $parcelStatus = $this->courierServiceProvider->getParcelStatusHistory(
                    new GetParcelStatusHistoryRequestDto($parcelAwbNumber)
                );

                if (!$hasRefreshedPackages) {
                    $this->packageHistoryStore->deleteByOrder($orderId);
                    $hasRefreshedPackages = true;
                }

                $this->packageHistoryStore->refresh(
                    $orderId,
                    $parcelAwbNumber,
                    $parcelStatus
                );
            } catch (CourierServiceException $exception) {
                $errors[] = sprintf('%s: %s', $parcelAwbNumber, $exception->getMessage());
            }
        }

        $packages = $this->packageHistoryStore->getForOrder($orderId);

        if ([] === $packages && [] !== $errors) {
            throw new RuntimeException(implode(' ', $errors));
        }

        return new ShowHistoryAwbResponse(
            $orderId,
            true,
            $packages
        );
    }
}
