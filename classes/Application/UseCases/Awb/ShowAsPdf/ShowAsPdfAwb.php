<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowAsPdfRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;

final class ShowAsPdfAwb
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
     * @var CarrierSettingsProviderInterface $carrierSettingsProvider
     */
    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    /**
     * @param OrderAwbStoreServiceProviderInterface $orderAwbStore
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param CarrierSettingsProviderInterface $carrierSettingsProvider
     */
    public function __construct(
        OrderAwbStoreServiceProviderInterface $orderAwbStore,
        CourierServiceProviderInterface $courierServiceProvider,
        CarrierSettingsProviderInterface $carrierSettingsProvider
    ) {
        $this->orderAwbStore = $orderAwbStore;
        $this->courierServiceProvider = $courierServiceProvider;
        $this->carrierSettingsProvider = $carrierSettingsProvider;
    }

    /**
     * @param ShowAsPdfAwbRequest $request
     *
     * @return ShowAsPdfAwbResponse
     */
    public function execute(ShowAsPdfAwbRequest $request): ShowAsPdfAwbResponse
    {
        $orderId = $request->getOrderId();
        $awb = $this->orderAwbStore->getByOrderId($orderId);

        if (null === $awb) {
            return new ShowAsPdfAwbResponse(
                'AWB not found for this order.',
                true,
                $orderId
            );
        }

        try {
            $pdfResponse = $this->courierServiceProvider->showAsPdf(
                new ShowAsPdfRequestDto(
                    (string) $awb->getAwbNumber(),
                    $this->carrierSettingsProvider->get()->getDefaultLabelFormat()
                )
            );
        } catch (CourierServiceException $exception) {
            return new ShowAsPdfAwbResponse(
                $exception->getMessage(),
                true,
                $orderId
            );
        }

        return new ShowAsPdfAwbResponse(
            '',
            false,
            $orderId,
            $pdfResponse->getPdf()
        );
    }
}
