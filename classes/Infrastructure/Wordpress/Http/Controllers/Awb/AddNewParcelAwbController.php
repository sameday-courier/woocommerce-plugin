<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use JsonException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Common\Services\AwbErrorParser;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwbRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

if (!defined("ABSPATH")) {
    exit;
}

final class AddNewParcelAwbController extends AbstractController
{
    private const ACTION = "add-new-parcel";

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
     *
     * @throws SamedaySDKException
     * @throws JsonException
     */
    protected function processAction(array $inputParams): void
    {
        $addNewParcelAwbItem = AddNewParcelAwbItem::fromArray($inputParams);
        $orderId = $addNewParcelAwbItem->getOrderId();

        try {
            $sameday = new Sameday(SdkInitiator::init());
        } catch (SamedaySDKException $e) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($e->getMessage()),
            );

            Redirector::to(
                'post.php',
                [
                    'post' => $orderId,
                    'action' => 'edit',
                    'add-new-parcel' => ResponseNoticeType::ERROR,
                ]
            );
        }

        $addNewParcelAwbRequest = new AddNewParcelAwbRequest(
            $addNewParcelAwbItem,
            $sameday,
            new SamedayAwbRepository(),
            new AwbErrorParser(),
            new ParcelDimensionsFactory()
        );

        $addNewParcelAwb = new AddNewParcelAwb($addNewParcelAwbRequest);

        $result = $addNewParcelAwb->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $result->getNoticeType(),
            );
        }

        Redirector::to(
            'post.php',
            [
                'post' => $result->getOrderId(),
                'action' => 'edit',
                'add-new-parcel' => $result->getNoticeType(),
            ]
        );
    }
}
