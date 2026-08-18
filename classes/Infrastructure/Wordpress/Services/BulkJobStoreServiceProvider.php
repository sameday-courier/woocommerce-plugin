<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Interfaces\CacheHandlerInterface;

final class BulkJobStoreServiceProvider implements BulkJobStoreInterface
{
    private const KEY_PREFIX = 'sameday_bulk_job_';

    private const TTL_SECONDS = 3600;

    /**
     * @var CacheHandlerInterface|CacheHandler
     */
    private CacheHandlerInterface $cacheHandler;

    public function __construct(
        ?CacheHandlerInterface $cacheHandler = null
    ) {
        $this->cacheHandler = $cacheHandler ?? new CacheHandler();
    }

    public function create(BulkJobDto $job): void
    {
        $this->cacheHandler->refreshCachedData(
            $this->buildKey($job->getJobId(), $job->getUserId()),
            $job->toArray(),
            self::TTL_SECONDS
        );
    }

    /**
     * @param string $jobId
     * @param int $userId
     *
     * @return BulkJobDto|null
     */
    public function get(string $jobId, int $userId): ?BulkJobDto
    {
        $data = $this->cacheHandler->getCachedData($this->buildKey($jobId, $userId));
        if ([] === $data) {
            return null;
        }

        $job = BulkJobDto::fromArray($data);
        if ($job->getJobId() !== $jobId || $job->getUserId() !== $userId) {
            return null;
        }

        return $job;
    }

    /**
     * @param BulkJobDto $job
     *
     * @return void
     */
    public function save(BulkJobDto $job): void
    {
        $this->cacheHandler->refreshCachedData(
            $this->buildKey($job->getJobId(), $job->getUserId()),
            $job->toArray(),
            self::TTL_SECONDS
        );
    }

    public function delete(string $jobId, int $userId): void
    {
        $this->cacheHandler->invalidateCachedData($this->buildKey($jobId, $userId));
    }

    private function buildKey(string $jobId, int $userId): string
    {
        return self::KEY_PREFIX . $userId . '_' . $jobId;
    }
}
