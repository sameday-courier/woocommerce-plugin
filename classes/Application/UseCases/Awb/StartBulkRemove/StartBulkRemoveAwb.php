<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\StartBulkRemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\Ports\StartBulkRemoveAwbServiceProviderInterface;

final class StartBulkRemoveAwb
{
    private StartBulkRemoveAwbItem $startBulkRemoveAwbItem;

    private StartBulkRemoveAwbServiceProviderInterface $startBulkRemoveAwbServiceProvider;

    public function __construct(StartBulkRemoveAwbRequest $request)
    {
        $this->startBulkRemoveAwbItem = $request->getStartBulkRemoveAwbItem();
        $this->startBulkRemoveAwbServiceProvider = $request->getStartBulkRemoveAwbServiceProvider();
    }

    public function execute(): StartBulkRemoveAwbResponse
    {
        $response = $this->startBulkRemoveAwbServiceProvider->start(
            new StartBulkRemoveAwbRequestDto(
                $this->startBulkRemoveAwbItem->getOrderIds(),
                $this->startBulkRemoveAwbItem->getUserId()
            )
        );

        if (!$response->isSuccess()) {
            return new StartBulkRemoveAwbResponse(
                $response->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new StartBulkRemoveAwbResponse(
            null,
            ResponseNoticeType::SUCCESS,
            $response->getJobId(),
            $response->getTotal(),
            $response->getProcessed(),
            $response->isDone()
        );
    }
}
