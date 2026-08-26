<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooGenerateAwbOrderProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderWeightCalculator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractRecursiveBulkController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\GenerateAwbFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\GenerateAwbRequestFromOrderFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayServiceRepository;

final class BulkGenerateAwbController extends AbstractRecursiveBulkController
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
     * @param int $itemId
     *
     * @return array{status: string, message: string, awbNumber: string|null}
     */
    protected function processItem(int $itemId): array
    {
        $dbHandler = new DbHandler();
        $samedayAwbRepository = new SamedayAwbRepository($dbHandler);
        $samedayServiceRepository = new SamedayServiceRepository($dbHandler);
        $requestFactory = new GenerateAwbRequestFromOrderFactory(
            new WooGenerateAwbOrderProvider(),
            new WooOrderWeightCalculator(),
            new WooOpenPackageOrderDataHandler(),
            new SamedayPickupPointRepository($dbHandler),
            $samedayServiceRepository,
            new CarrierServiceRules($samedayServiceRepository),
        );

        try {
            $generateAwb = GenerateAwbFactory::create();
            $result = $generateAwb->execute(
                $requestFactory->fromOrderId($itemId)
            );

            $status = $result->hasError()
                ? ResponseNoticeType::ERROR
                : ResponseNoticeType::SUCCESS;
            $message = $result->hasError()
                ? TranslatorHandler::translate($result->getNoticeMessage())
                : TranslatorHandler::translate('Successfully generated.');

            $awbNumber = null;
            if (ResponseNoticeType::SUCCESS === $status) {
                $awb = $samedayAwbRepository->getAwbForOrderId($itemId);
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
}
