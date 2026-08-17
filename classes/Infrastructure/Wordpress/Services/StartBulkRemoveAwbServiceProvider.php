<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\StartBulkRemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\StartBulkRemoveAwbResponseDto;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;
use SamedayCourier\Shipping\Domain\Ports\StartBulkRemoveAwbServiceProviderInterface;
use Throwable;

final class StartBulkRemoveAwbServiceProvider implements StartBulkRemoveAwbServiceProviderInterface
{
    private BulkJobStoreInterface $bulkJobStore;

    public function __construct(?BulkJobStoreInterface $bulkJobStore = null)
    {
        $this->bulkJobStore = $bulkJobStore ?? new BulkJobStoreServiceProvider();
    }

    /**
     * @param StartBulkRemoveAwbRequestDto $startBulkRemoveAwbRequestDto
     *
     * @return StartBulkRemoveAwbResponseDto
     */
    public function start(StartBulkRemoveAwbRequestDto $startBulkRemoveAwbRequestDto): StartBulkRemoveAwbResponseDto
    {
        $orderIds = $startBulkRemoveAwbRequestDto->getOrderIds();
        if ([] === $orderIds) {
            return new StartBulkRemoveAwbResponseDto(
                false,
                'There is no data to process.'
            );
        }

        try {
            $jobId = $this->generateJobId();
        } catch (Throwable $exception) {
            return new StartBulkRemoveAwbResponseDto(
                false,
                'Unable to start bulk job.'
            );
        }

        $job = BulkJobDto::create(
            $jobId,
            $startBulkRemoveAwbRequestDto->getUserId(),
            $orderIds
        );

        $this->bulkJobStore->create($job);

        return new StartBulkRemoveAwbResponseDto(
            true,
            null,
            $job->getJobId(),
            $job->getTotal(),
            0,
            false
        );
    }

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
