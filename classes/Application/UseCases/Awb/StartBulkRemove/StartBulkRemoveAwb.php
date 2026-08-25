<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Domain\Ports\BulkJobIdGeneratorInterface;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartBulkRemoveAwb
{
    private StartBulkRemoveAwbItem $startBulkRemoveAwbItem;

    private BulkJobStoreInterface $bulkJobStore;

    private BulkJobIdGeneratorInterface $bulkJobIdGenerator;

    /**
     * @param StartBulkRemoveAwbRequest $request
     */
    public function __construct(StartBulkRemoveAwbRequest $request)
    {
        $this->startBulkRemoveAwbItem = $request->getStartBulkRemoveAwbItem();
        $this->bulkJobStore = $request->getBulkJobStore();
        $this->bulkJobIdGenerator = $request->getBulkJobIdGenerator();
    }

    /**
     * @return StartBulkRemoveAwbResponse
     */
    public function execute(): StartBulkRemoveAwbResponse
    {
        $orderIds = $this->startBulkRemoveAwbItem->getOrderIds();
        if ([] === $orderIds) {
            return new StartBulkRemoveAwbResponse(
                'There is no data to process.',
                ResponseNoticeType::ERROR
            );
        }

        $job = BulkJobDto::create(
            $this->bulkJobIdGenerator->generate(),
            $this->startBulkRemoveAwbItem->getUserId(),
            $orderIds
        );

        $this->bulkJobStore->create($job);

        return new StartBulkRemoveAwbResponse(
            null,
            ResponseNoticeType::SUCCESS,
            $job->getJobId(),
            $job->getTotal(),
            0,
            false
        );
    }
}
