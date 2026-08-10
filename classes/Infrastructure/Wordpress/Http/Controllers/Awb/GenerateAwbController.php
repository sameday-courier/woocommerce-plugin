<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayPickupPointRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Woo\WooOrderAddressRepository;
use SamedayCourier\Shipping\Application\Common\Factories\BillingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\LockerDtoFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Application\Common\Factories\ShippingDtoFactory;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Application\Common\Services\AwbRemover;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbRequest;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\AwbGenerateServiceTaxResolver;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\Validators\Awb\Generate\GenerateAwbValidator;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressArchive;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderShippingAddressUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSamedayShippingHdAddressParser;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooShippingHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined("ABSPATH")) {
    exit;
}

final class GenerateAwbController extends AbstractController
{
    private const ACTION = "add-awb";

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
     * @throws JsonException
     */
    protected function processAction(array $inputParams): void
    {
        $orderId = (int) $inputParams['samedaycourier-order-id'];
        $orderData = (new WooShippingHandler())->getShippingOrderById($orderId);

        if (empty($orderData)) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate("There is no data to process."),
            );

            Redirector::to(
                'post.php',
                [
                    'id' => $orderId,
                    'post' => $orderId,
                    'action' => 'edit',
                ]
            );
        }

        $data = array_merge($inputParams, $orderData);

        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($exception->getMessage()),
            );

            Redirector::to(
                'post.php',
                [
                    'post' => $orderId,
                    'action' => 'edit',
                    'add-awb' => ResponseNoticeType::ERROR,
                ]
            );
        }

        $dbHandler = new DbHandler();
        $hdAddressParser = new WooSamedayShippingHdAddressParser();
        $stateCodeResolver = new WooStateCodeResolver(new WooCountriesHandler());
        $samedayLockerRepository = new SamedayLockerRepository($dbHandler);
        $samedayServiceRepository = new SamedayServiceRepository($dbHandler);
        $samedayAwbRepository = new SamedayAwbRepository($dbHandler);
        $samedayServiceRules = new SamedayServiceRules($samedayServiceRepository);
        $generateAwb = new GenerateAwb(
            new GenerateAwbRequest(
                GenerateAwbItem::fromArray($data),
                $samedayApiClient,
                $dbHandler,
                $samedayServiceRepository,
                new SamedayPickupPointRepository($dbHandler),
                $samedayAwbRepository,
                new WooOrderShippingAddressUpdater(
                    new WooOrderAddressRepository($dbHandler),
                    new WooOrderShippingAddressArchive(),
                    $samedayLockerRepository,
                    $hdAddressParser,
                    $stateCodeResolver,
                ),
                new AwbErrorParser(),
                $hdAddressParser,
                $stateCodeResolver,
                new ParcelDimensionsFactory(),
                new LockerDtoFactory($samedayLockerRepository),
                new ShippingDtoFactory(),
                new BillingDtoFactory(),
                new GenerateAwbValidator(),
                new AwbGenerateServiceTaxResolver($samedayServiceRepository),
                $samedayServiceRules,
                new AwbRemover($samedayApiClient, $samedayAwbRepository),
            )
        );

        $result = $generateAwb->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $result->getNoticeType(),
            );
        }

        Redirector::to(
            'post.php',
            [
                'id' => $orderId,
                'post' => $orderId,
                'action' => 'edit',
            ]
        );
    }
}
