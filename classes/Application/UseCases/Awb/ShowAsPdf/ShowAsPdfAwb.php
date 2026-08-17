<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowAsPdfAwbRequestDto;
use SamedayCourier\Shipping\Domain\Ports\ShowAsPdfAwbServiceProviderInterface;

final class ShowAsPdfAwb
{
    private ShowAsPdfAwbItem $showAsPdfAwbItem;

    private ShowAsPdfAwbServiceProviderInterface $showAsPdfAwbServiceProvider;

    public function __construct(ShowAsPdfAwbRequest $showAsPdfAwbRequest)
    {
        $this->showAsPdfAwbItem = $showAsPdfAwbRequest->getShowAsPdfAwbItem();
        $this->showAsPdfAwbServiceProvider = $showAsPdfAwbRequest->getShowAsPdfAwbServiceProvider();
    }

    /**
     * @return ShowAsPdfAwbResponse
     */
    public function execute(): ShowAsPdfAwbResponse
    {
        $showAsPdfAwbResponse = $this->showAsPdfAwbServiceProvider->showAsPdf(
            new ShowAsPdfAwbRequestDto($this->showAsPdfAwbItem->getOrderId())
        );

        if (!$showAsPdfAwbResponse->isSuccess()) {
            return new ShowAsPdfAwbResponse(
                $showAsPdfAwbResponse->getOrderId(),
                $showAsPdfAwbResponse->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new ShowAsPdfAwbResponse(
            $showAsPdfAwbResponse->getOrderId(),
            null,
            ResponseNoticeType::SUCCESS,
            $showAsPdfAwbResponse->getPdf(),
        );
    }
}
