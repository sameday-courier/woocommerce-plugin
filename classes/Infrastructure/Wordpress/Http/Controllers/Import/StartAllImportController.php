<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Import;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Import\StartAllImport\StartAllImport;
use SamedayCourier\Shipping\Application\UseCases\Import\StartAllImport\StartAllImportRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobIdGeneratorServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\BulkJobStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;

final class StartAllImportController extends AbstractController
{
    private const ACTION = 'all-import-start';

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
        $result = (new StartAllImport(
            new StartAllImportRequest(
                $this->getCurrentUserId(),
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
