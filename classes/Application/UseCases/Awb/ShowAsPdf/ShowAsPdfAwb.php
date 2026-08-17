<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowAsPdfRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;

final class ShowAsPdfAwb
{
    private ShowAsPdfAwbItem $showAsPdfAwbItem;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    public function __construct(ShowAsPdfAwbRequest $showAsPdfAwbRequest)
    {
        $this->showAsPdfAwbItem = $showAsPdfAwbRequest->getShowAsPdfAwbItem();
        $this->orderAwbStore = $showAsPdfAwbRequest->getOrderAwbStore();
        $this->courierServiceProvider = $showAsPdfAwbRequest->getCourierServiceProvider();
        $this->carrierSettingsProvider = $showAsPdfAwbRequest->getCarrierSettingsProvider();
    }

    public function execute(): ShowAsPdfAwbResponse
    {
        $orderId = $this->showAsPdfAwbItem->getOrderId();
        $awb = $this->orderAwbStore->getByOrderId($orderId);

        if (null === $awb) {
            return new ShowAsPdfAwbResponse(
                $orderId,
                'AWB not found for this order.',
                ResponseNoticeType::ERROR
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
                $orderId,
                $exception->getMessage(),
                ResponseNoticeType::ERROR
            );
        }

        return new ShowAsPdfAwbResponse(
            $orderId,
            null,
            ResponseNoticeType::SUCCESS,
            $pdfResponse->getPdf()
        );
    }
}
