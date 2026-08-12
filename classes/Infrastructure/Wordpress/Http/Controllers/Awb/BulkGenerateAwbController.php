<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Factories\AwbRequestFactory;
use SamedayCourier\Shipping\Application\Common\Factories\GenerateAwbItemFactory;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwb;
use SamedayCourier\Shipping\Domain\DTOs\BulkAwbJob;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooGenerateAwbOrderProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderAwbProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderWeightCalculator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TransientBulkAwbJobStore;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

if (!defined('ABSPATH')) {
    exit;
}

final class BulkGenerateAwbController extends AbstractController
{
    private const ACTION = 'bulk-generate-awb-next';

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

        $dbHandler = new DbHandler();
        $samedayAwbRepository = new SamedayAwbRepository($dbHandler);
        $generateAwbItemFactory = new GenerateAwbItemFactory(
            new WooGenerateAwbOrderProvider(),
            new WooOrderWeightCalculator(),
            new WooOpenPackageOrderDataHandler(),
            new WooOrderAwbProvider($samedayAwbRepository),
            new SamedayPickupPointRepository($dbHandler),
            new SamedayServiceRules(new SamedayServiceRepository($dbHandler)),
        );

        try {
            $generateAwbItem = $generateAwbItemFactory->fromOrderId($orderId);
            $result = (new GenerateAwb(
                (new AwbRequestFactory())->create($generateAwbItem, $samedayApiClient)
            ))->execute();

            $status = $result->hasNotices()
                ? $result->getNoticeType()
                : ResponseNoticeType::SUCCESS;
            $message = $result->hasNotices()
                ? TranslatorHandler::translate($result->getNoticeMessage() ?? '')
                : TranslatorHandler::translate('Successfully generated.');

            $awbNumber = null;
            if (ResponseNoticeType::SUCCESS === $status) {
                $awb = $samedayAwbRepository->getAwbForOrderId($orderId);
                $awbNumber = null !== $awb ? $awb->getAwbNumber() : null;
                $message = TranslatorHandler::translate('Successfully generated.');
            }

            return [
                'status' => $status,
                'message' => $message,
                'awbNumber' => $awbNumber,
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
