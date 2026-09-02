<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use RuntimeException;
use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

use SamedayCourier\Shipping\Domain\DTOs\Requests\GetParcelStatusHistoryRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PackageHistoryStoreServiceProviderInterface;

/**
 * @extends AbstractUseCase<ShowHistoryAwbRequest, ShowHistoryAwbResponse>
 *
 * @method ShowHistoryAwbResponse execute(ShowHistoryAwbRequest $request)
 */
final class ShowHistoryAwb extends AbstractUseCase
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
     * @var PackageHistoryStoreServiceProviderInterface $packageHistoryStore
     */
    private PackageHistoryStoreServiceProviderInterface $packageHistoryStore;

    /**
     * @param OrderAwbStoreServiceProviderInterface $orderAwbStore
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param PackageHistoryStoreServiceProviderInterface $packageHistoryStore
     */
    public function __construct(
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider,
        PackageHistoryStoreServiceProviderInterface $packageHistoryStore
    ) {
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
        $this->packageHistoryStore = $packageHistoryStore;
    }

    /**
     * @param ShowHistoryAwbRequest $request
     *
     * @return ShowHistoryAwbResponse
     */
    protected function processAction(RequestInterface $request): ShowHistoryAwbResponse
    {
        $orderId = $request->getOrderId();
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
