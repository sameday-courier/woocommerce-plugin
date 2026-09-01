<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Import\StartAllImport;

use SamedayCourier\Shipping\Domain\AllImportSteps;
use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Domain\Ports\BulkJobIdGeneratorInterface;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartAllImport
{
    /**
     * @var BulkJobStoreInterface $bulkJobStore
     */
    private BulkJobStoreInterface $bulkJobStore;

    /**
     * @var BulkJobIdGeneratorInterface $bulkJobIdGenerator
     */
    private BulkJobIdGeneratorInterface $bulkJobIdGenerator;

    /**
     * @param BulkJobStoreInterface $bulkJobStore
     * @param BulkJobIdGeneratorInterface $bulkJobIdGenerator
     */
    public function __construct(
        BulkJobStoreInterface $bulkJobStore,
        BulkJobIdGeneratorInterface $bulkJobIdGenerator
    ) {
        $this->bulkJobStore = $bulkJobStore;
        $this->bulkJobIdGenerator = $bulkJobIdGenerator;
    }

    /**
     * @param StartAllImportRequest $request
     * @return StartAllImportResponse
     */
    public function execute(StartAllImportRequest $request): StartAllImportResponse
    {
        $itemIds = AllImportSteps::ids();
        if ([] === $itemIds) {
            return new StartAllImportResponse(
                'There is no data to process.',
                true
            );
        }

        $job = BulkJobDto::create(
            $this->bulkJobIdGenerator->generate(),
            $request->getUserId(),
            $itemIds
        );

        $this->bulkJobStore->create($job);

        return new StartAllImportResponse(
            '',
            false,
            $job->getJobId(),
            $job->getTotal(),
            0,
            false
        );
    }
}
