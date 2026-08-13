<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Factories\AwbRequestFactory;
use SamedayCourier\Shipping\Application\Common\Factories\GenerateAwbItemFactory;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwb;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooGenerateAwbOrderProvider;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOpenPackageOrderDataHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderWeightCalculator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractRecursiveBulkController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
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
     * @return array{status: string, message: string, ...}
     */
    protected function processItem(int $itemId): array
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
        $samedayServiceRepository = new SamedayServiceRepository($dbHandler);
        $generateAwbItemFactory = new GenerateAwbItemFactory(
            new WooGenerateAwbOrderProvider(),
            new WooOrderWeightCalculator(),
            new WooOpenPackageOrderDataHandler(),
            new SamedayPickupPointRepository($dbHandler),
            $samedayServiceRepository,
            new SamedayServiceRules($samedayServiceRepository),
        );

        try {
            $generateAwbItem = $generateAwbItemFactory->fromOrderId($itemId);
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
