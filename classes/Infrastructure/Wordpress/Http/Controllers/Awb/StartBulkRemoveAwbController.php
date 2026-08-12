<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use SamedayCourier\Shipping\Application\UseCases\Awb\BulkRemove\BulkRemoveAwbItem;
use SamedayCourier\Shipping\Domain\DTOs\BulkAwbJob;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TransientBulkAwbJobStore;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class StartBulkRemoveAwbController extends AbstractController
{
    private const ACTION = 'bulk-remove-awb-start';

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
        $orderIds = BulkRemoveAwbItem::fromArray($inputParams)->getOrderIds();

        if ([] === $orderIds) {
            wp_send_json_error(
                [
                    'message' => TranslatorHandler::translate('There is no data to process.'),
                ],
                400
            );
        }

        $jobId = wp_generate_uuid4();
        $job = BulkAwbJob::create(
            $jobId,
            $this->getCurrentUserId(),
            $orderIds
        );

        (new TransientBulkAwbJobStore())->create($job);

        wp_send_json_success([
            'jobId' => $jobId,
            'total' => $job->getTotal(),
            'processed' => 0,
            'done' => false,
        ]);
    }
}
