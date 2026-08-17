<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\StartBulkGenerateAwbRequestDto;
use SamedayCourier\Shipping\Domain\Ports\StartBulkGenerateAwbServiceProviderInterface;

final class StartBulkGenerateAwb
{
    private StartBulkGenerateAwbItem $startBulkGenerateAwbItem;

    private StartBulkGenerateAwbServiceProviderInterface $startBulkGenerateAwbServiceProvider;

    public function __construct(StartBulkGenerateAwbRequest $request)
    {
        $this->startBulkGenerateAwbItem = $request->getStartBulkGenerateAwbItem();
        $this->startBulkGenerateAwbServiceProvider = $request->getStartBulkGenerateAwbServiceProvider();
    }

    public function execute(): StartBulkGenerateAwbResponse
    {
        $response = $this->startBulkGenerateAwbServiceProvider->start(
            new StartBulkGenerateAwbRequestDto(
                $this->startBulkGenerateAwbItem->getOrderIds(),
                $this->startBulkGenerateAwbItem->getUserId()
            )
        );

        if (!$response->isSuccess()) {
            return new StartBulkGenerateAwbResponse(
                $response->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new StartBulkGenerateAwbResponse(
            null,
            ResponseNoticeType::SUCCESS,
            $response->getJobId(),
            $response->getTotal(),
            $response->getProcessed(),
            $response->isDone()
        );
    }
}
