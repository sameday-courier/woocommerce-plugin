<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Import\StartAllImport;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\AllImportSteps;
use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Domain\Ports\BulkJobIdGeneratorInterface;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartAllImport
{
    private int $userId;

    private BulkJobStoreInterface $bulkJobStore;

    private BulkJobIdGeneratorInterface $bulkJobIdGenerator;

    /**
     * @param StartAllImportRequest $request
     */
    public function __construct(StartAllImportRequest $request)
    {
        $this->userId = $request->getUserId();
        $this->bulkJobStore = $request->getBulkJobStore();
        $this->bulkJobIdGenerator = $request->getBulkJobIdGenerator();
    }

    /**
     * @return StartAllImportResponse
     */
    public function execute(): StartAllImportResponse
    {
        $itemIds = AllImportSteps::ids();
        if ([] === $itemIds) {
            return new StartAllImportResponse(
                'There is no data to process.',
                ResponseNoticeType::ERROR,
            );
        }

        $job = BulkJobDto::create(
            $this->bulkJobIdGenerator->generate(),
            $this->userId,
            $itemIds
        );

        $this->bulkJobStore->create($job);

        return new StartAllImportResponse(
            null,
            ResponseNoticeType::SUCCESS,
            $job->getJobId(),
            $job->getTotal(),
            0,
            false
        );
    }
}
