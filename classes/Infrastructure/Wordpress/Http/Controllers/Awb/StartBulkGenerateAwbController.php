<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate\StartBulkGenerateAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\StartBulkGenerateAwbFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\StartBulkGenerateAwbMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

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
        $params = new StartBulkGenerateAwbMapper($inputParams);
        $startBulkGenerateAwb = StartBulkGenerateAwbFactory::create();

        $result = $startBulkGenerateAwb->execute(
            new StartBulkGenerateAwbRequest(
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
