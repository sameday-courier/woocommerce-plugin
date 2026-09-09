<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractRecursiveBulkController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RemoveAwbFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class BulkRemoveAwbController extends AbstractRecursiveBulkController
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
     * @param int $itemId
     *
     * @return array{status: string, message: string, awbNumber: string|null}
     */
    protected function processItem(int $itemId): array
    {
        $samedayAwbRepository = new SamedayAwbRepository();
        $orderAwbStore = new OrderAwbStoreServiceProvider($samedayAwbRepository);
        $awb = $orderAwbStore->getByOrderId($itemId);
        $awbNumber = null !== $awb ? $awb->getAwbNumber() : null;

        try {
            $removeAwb = RemoveAwbFactory::create();
            $result = $removeAwb->execute(
                new RemoveAwbRequest($itemId)
            );

            $status = $result->hasError()
                ? ResponseNoticeType::ERROR
                : ResponseNoticeType::SUCCESS;

            return [
                'status' => $status,
                'message' => TranslatorHandler::translate($result->getNoticeMessage()),
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
}
