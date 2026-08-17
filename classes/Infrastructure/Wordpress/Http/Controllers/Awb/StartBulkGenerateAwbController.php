<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate\StartBulkGenerateAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate\StartBulkGenerateAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate\StartBulkGenerateAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\StartBulkGenerateAwbServiceProvider;

final class StartBulkGenerateAwbController extends AbstractController
{
    private const ACTION = 'bulk-generate-awb-start';

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
        $result = (new StartBulkGenerateAwb(
            new StartBulkGenerateAwbRequest(
                StartBulkGenerateAwbItem::fromArray(
                    array_merge(
                        $inputParams,
                        ['samedaycourier-user-id' => $this->getCurrentUserId()]
                    )
                ),
                new StartBulkGenerateAwbServiceProvider()
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

        $this->sendJsonSuccessResponse([
            'jobId' => $result->getJobId(),
            'total' => $result->getTotal(),
            'processed' => $result->getProcessed(),
            'done' => $result->isDone(),
        ]);
    }
}
