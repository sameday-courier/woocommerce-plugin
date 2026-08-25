<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove\StartBulkRemoveAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove\StartBulkRemoveAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove\StartBulkRemoveAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobIdGeneratorServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobStoreServiceProvider;

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
        $result = (new StartBulkRemoveAwb(
            new StartBulkRemoveAwbRequest(
                StartBulkRemoveAwbItem::fromArray(
                    array_merge(
                        $inputParams,
                        ['samedaycourier-user-id' => $this->getCurrentUserId()]
                    )
                ),
                new BulkJobStoreServiceProvider(),
                new BulkJobIdGeneratorServiceProvider()
            )
        ))->execute();

        if (ResponseNoticeType::ERROR === $result->getNoticeType()) {
            $this->sendJsonErrorResponse(
                [
                    'message' => TranslatorHandler::translate(
                        $result->getNoticeMessage() ?? 'There is no data to process.'
                    ),
                ]
            );
        }

        $jobId = $result->getJobId();

        $this->sendJsonSuccessResponse([
            'jobId' => null !== $jobId ? $jobId->toString() : null,
            'total' => $result->getTotal(),
            'processed' => $result->getProcessed(),
            'done' => $result->isDone(),
        ]);
    }
}
