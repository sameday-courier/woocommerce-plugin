<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobStoreServiceProvider;

/**
 * Progressive bulk job step controller.
 * Each request processes one pending item; the client recurses until done=true.
 */
abstract class AbstractRecursiveBulkController extends AbstractController
{
    /**
     * @param array $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $jobId = isset($inputParams['jobId']) ? (string) $inputParams['jobId'] : '';

        if ('' === $jobId) {
            $this->sendJsonErrorResponse(
                'Invalid bulk job.',
            );
        }

        $userId = $this->getCurrentUserId();
        $jobStore = new BulkJobStoreServiceProvider();
        $job = $jobStore->get($jobId, $userId);

        if (null === $job) {
            $this->sendJsonErrorResponse(
                'Bulk session expired, please restart.',
                404
            );
        }

        if ($job->isDone()) {
            $this->sendDoneResponse($jobStore, $job);
        }

        $nextItem = $job->getNextUnprocessed();
        if (null === $nextItem) {
            $this->sendDoneResponse($jobStore, $job);
        }

        $itemId = $nextItem->getItemId();
        $payload = $this->processItem($itemId);
        $job = $job->withItemPayload($itemId, $payload);
        $jobStore->save($job);

        if ($job->isDone()) {
            $this->sendDoneResponse($jobStore, $job);
        }

        $this->sendJsonSuccessResponse(
            [
                'done' => false,
                'jobId' => $job->getJobId(),
                'total' => $job->getTotal(),
                'processed' => $job->getProcessedCount(),
                'currentItemId' => $itemId,
                'lastResult' => array_merge(
                    [
                        'itemId' => $itemId,
                    ],
                    $payload
                ),
                'successCount' => $job->getSuccessCount(),
                'errorCount' => $job->getErrorCount(),
            ]
        );
    }

    /**
     * @return array{status: string, message: string, ...}
     */
    abstract protected function processItem(int $itemId): array;

    /**
     * @param BulkJobStoreServiceProvider $jobStore
     * @param BulkJobDto $job
     *
     * @return void
     */
    private function sendDoneResponse(BulkJobStoreServiceProvider $jobStore, BulkJobDto $job): void
    {
        $payload = [
            'done' => true,
            'jobId' => $job->getJobId(),
            'total' => $job->getTotal(),
            'processed' => $job->getProcessedCount(),
            'successCount' => $job->getSuccessCount(),
            'errorCount' => $job->getErrorCount(),
            'status' => $job->getErrorCount() > 0
                ? ResponseNoticeType::ERROR
                : ResponseNoticeType::SUCCESS,
            'items' => $job->toReportItems(),
        ];

        $jobStore->delete($job->getJobId(), $job->getUserId());

        if ($job->getErrorCount() > 0 && 0 === $job->getSuccessCount()) {
            $this->sendJsonErrorResponse($payload);
        }

        $this->sendJsonSuccessResponse($payload);
    }
}
