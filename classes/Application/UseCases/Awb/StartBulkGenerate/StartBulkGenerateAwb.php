<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Domain\Ports\BulkJobIdGeneratorInterface;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartBulkGenerateAwb
{
    private StartBulkGenerateAwbItem $startBulkGenerateAwbItem;

    private BulkJobStoreInterface $bulkJobStore;

    private BulkJobIdGeneratorInterface $bulkJobIdGenerator;

    /**
     * @param StartBulkGenerateAwbRequest $request
     */
    public function __construct(StartBulkGenerateAwbRequest $request)
    {
        $this->startBulkGenerateAwbItem = $request->getStartBulkGenerateAwbItem();
        $this->bulkJobStore = $request->getBulkJobStore();
        $this->bulkJobIdGenerator = $request->getBulkJobIdGenerator();
    }

    /**
     * @return StartBulkGenerateAwbResponse
     */
    public function execute(): StartBulkGenerateAwbResponse
    {
        $orderIds = $this->startBulkGenerateAwbItem->getOrderIds();
        if ([] === $orderIds) {
            return new StartBulkGenerateAwbResponse(
                'There is no data to process.',
                ResponseNoticeType::ERROR
            );
        }

        $job = BulkJobDto::create(
            $this->bulkJobIdGenerator->generate(),
            $this->startBulkGenerateAwbItem->getUserId(),
            $orderIds
        );

        $this->bulkJobStore->create($job);

        return new StartBulkGenerateAwbResponse(
            null,
            ResponseNoticeType::SUCCESS,
            $job->getJobId(),
            $job->getTotal(),
            0,
            false
        );
    }
}
