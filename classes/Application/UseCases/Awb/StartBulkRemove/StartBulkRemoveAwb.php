<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;
use Throwable;

final class StartBulkRemoveAwb
{
    private StartBulkRemoveAwbItem $startBulkRemoveAwbItem;

    private BulkJobStoreInterface $bulkJobStore;

    /**
     * @param StartBulkRemoveAwbRequest $request
     */
    public function __construct(StartBulkRemoveAwbRequest $request)
    {
        $this->startBulkRemoveAwbItem = $request->getStartBulkRemoveAwbItem();
        $this->bulkJobStore = $request->getBulkJobStore();
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

        try {
            $jobId = $this->generateJobId();
        } catch (Throwable $exception) {
            return new StartBulkRemoveAwbResponse(
                'Unable to start bulk job.',
                ResponseNoticeType::ERROR
            );
        }

        $job = BulkJobDto::create(
            $jobId,
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

    /**
     * @return string
     */
    private function generateJobId(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(bin2hex($data), 4)
        );
    }
}
