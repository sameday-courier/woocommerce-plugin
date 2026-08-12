<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\Common\Services\AwbRemover;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbRequest;
use SamedayCourier\Shipping\Domain\DTOs\BulkAwbJob;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TransientBulkAwbJobStore;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class BulkRemoveAwbController extends AbstractController
{
    private const ACTION = 'bulk-remove-awb-next';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

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
                    'message' => TranslatorHandler::translate('Invalid bulk AWB job.'),
                ],
                400
            );
        }

        $userId = $this->getCurrentUserId();
        $jobStore = new TransientBulkAwbJobStore();
        $job = $jobStore->get($jobId, $userId);

        if (null === $job) {
            wp_send_json_error(
                [
                    'message' => TranslatorHandler::translate('Bulk AWB session expired, please restart.'),
                ],
                404
            );
        }

        if ($job->isDone()) {
            $this->sendDoneResponse($jobStore, $job);
        }

        $nextOrder = $job->getNextUnprocessed();
        if (null === $nextOrder) {
            $this->sendDoneResponse($jobStore, $job);
        }

        $orderId = $nextOrder->getOrderId();
        $payload = $this->processOrder($orderId);
        $job = $job->withOrderPayload($orderId, $payload);
        $jobStore->save($job);

        if ($job->isDone()) {
            $this->sendDoneResponse($jobStore, $job);
        }

        wp_send_json_success([
            'done' => false,
            'jobId' => $job->getJobId(),
            'total' => $job->getTotal(),
            'processed' => $job->getProcessedCount(),
            'currentOrderId' => $orderId,
            'lastResult' => [
                'orderId' => $orderId,
                'status' => $payload['status'],
                'message' => $payload['message'],
            ],
            'successCount' => $job->getSuccessCount(),
            'errorCount' => $job->getErrorCount(),
        ]);
    }

    /**
     * @return array{status: string, message: string, awbNumber?: string|null}
     */
    private function processOrder(int $orderId): array
    {
        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (Exception $exception) {
            return [
                'status' => ResponseNoticeType::ERROR,
                'message' => TranslatorHandler::translate($exception->getMessage()),
                'awbNumber' => null,
            ];
        }

        $samedayAwbRepository = new SamedayAwbRepository();
        $awb = $samedayAwbRepository->getAwbForOrderId($orderId);
        $awbNumber = null !== $awb ? $awb->getAwbNumber() : null;

        try {
            $result = (new RemoveAwb(
                new RemoveAwbRequest(
                    new RemoveAwbItem($orderId),
                    new AwbRemover($samedayApiClient, $samedayAwbRepository),
                    new AwbErrorParser()
                )
            ))->execute();

            $status = $result->hasNotices()
                ? $result->getNoticeType()
                : ResponseNoticeType::SUCCESS;
            $message = $result->hasNotices()
                ? TranslatorHandler::translate($result->getNoticeMessage() ?? '')
                : TranslatorHandler::translate('Successfully removed.');

            if (ResponseNoticeType::SUCCESS === $status) {
                $message = TranslatorHandler::translate('Successfully removed.');
            }

            return [
                'status' => $status,
                'message' => $message,
                'awbNumber' => ResponseNoticeType::SUCCESS === $status ? $awbNumber : null,
            ];
        } catch (Exception $exception) {
            return [
                'status' => ResponseNoticeType::ERROR,
                'message' => TranslatorHandler::translate($exception->getMessage()),
                'awbNumber' => null,
            ];
        }
    }

    private function sendDoneResponse(TransientBulkAwbJobStore $jobStore, BulkAwbJob $job): void
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
            'orders' => $job->toReportOrders(),
        ];

        $jobStore->delete($job->getJobId(), $job->getUserId());

        if ($job->getErrorCount() > 0 && 0 === $job->getSuccessCount()) {
            wp_send_json_error($payload, 400);
        }

        wp_send_json_success($payload);
    }
}
