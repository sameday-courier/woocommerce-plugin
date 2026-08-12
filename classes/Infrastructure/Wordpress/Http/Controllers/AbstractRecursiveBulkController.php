<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\BulkJob;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TransientBulkJobStore;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;

if (!defined('ABSPATH')) {
    exit;
}

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
            wp_send_json_error(
                [
                    'message' => TranslatorHandler::translate('Invalid bulk job.'),
                ],
                400
            );
        }

        $userId = $this->getCurrentUserId();
        $jobStore = new TransientBulkJobStore();
        $job = $jobStore->get($jobId, $userId);

        if (null === $job) {
            wp_send_json_error(
                [
                    'message' => TranslatorHandler::translate('Bulk session expired, please restart.'),
                ],
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

        wp_send_json_success([
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
        ]);
    }

    /**
     * Process a single bulk job item (one recursive step).
     *
     * @return array{status: string, message: string, ...}
     */
    abstract protected function processItem(int $itemId): array;

    /**
     * @param TransientBulkJobStore $jobStore
     * @param BulkJob $job
     *
     * @return void
     */
    private function sendDoneResponse(TransientBulkJobStore $jobStore, BulkJob $job): void
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
            wp_send_json_error($payload, 400);
        }

        wp_send_json_success($payload);
    }
}
