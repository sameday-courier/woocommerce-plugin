<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove\StartBulkRemoveAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\StartBulkRemoveAwbFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\StartBulkGenerateAwbMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

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
        $params = new StartBulkGenerateAwbMapper($inputParams);
        $startBulkRemoveAwb = StartBulkRemoveAwbFactory::create();

        $result = $startBulkRemoveAwb->execute(
            new StartBulkRemoveAwbRequest(
                $params->orderIds(),
                $this->getCurrentUserId()
            )
        );

        if ($result->hasError()) {
            $this->sendJsonErrorResponse(
                [
                    'message' => TranslatorHandler::translate(
                        $result->getNoticeMessage()
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
