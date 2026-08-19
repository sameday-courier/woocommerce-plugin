<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Remove\RemoveAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractRecursiveBulkController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PostRemoveAwbServiceProvider;
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
     * @return array{status:
     */
    protected function processItem(int $itemId): array
    {
        $samedayAwbRepository = new SamedayAwbRepository();
        $orderAwbStore = new OrderAwbStoreServiceProvider($samedayAwbRepository);
        $awb = $orderAwbStore->getByOrderId($itemId);
        $awbNumber = null !== $awb ? $awb->getAwbNumber() : null;

        try {
            $result = (new RemoveAwb(
                new RemoveAwbRequest(
                    new RemoveAwbItem($itemId),
                    $orderAwbStore,
                    new CourierServiceProvider(),
                    new PostRemoveAwbServiceProvider($samedayAwbRepository)
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
}
