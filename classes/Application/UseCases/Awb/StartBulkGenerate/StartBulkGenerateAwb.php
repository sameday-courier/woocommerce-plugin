<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate;

use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Domain\Ports\BulkJobIdGeneratorInterface;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartBulkGenerateAwb
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
     * @param StartBulkGenerateAwbRequest $request
     * @return StartBulkGenerateAwbResponse
     */
    public function execute(StartBulkGenerateAwbRequest $request): StartBulkGenerateAwbResponse
    {
        $orderIds = $request->getOrderIds();
        $userId = $request->getUserId();

        if ([] === $orderIds) {
            return new StartBulkGenerateAwbResponse(
                'There is no data to process.',
                true
            );
        }

        $job = BulkJobDto::create(
            $this->bulkJobIdGenerator->generate(),
            $userId,
            $orderIds
        );

        $this->bulkJobStore->create($job);

        return new StartBulkGenerateAwbResponse(
            '',
            false,
            $job->getJobId(),
            $job->getTotal(),
            0,
            false
        );
    }
}
