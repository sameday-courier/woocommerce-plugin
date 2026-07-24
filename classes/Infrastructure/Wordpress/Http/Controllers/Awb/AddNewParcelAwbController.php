<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use JsonException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\ParcelDimensionsObject;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel\AddNewParcelAwbRequest;
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
    protected function processPostAction(array $inputParams): void
    {
        $addNewParcelAwb = new AddNewParcelAwb(
            new AddNewParcelAwbRequest(
                $inputParams['sameday_order_id'],
                new AddNewParcelAwbItem(
                    new ParcelDimensionsObject(
                        (float) number_format((float) $inputParams['samedaycourier-parcel-weight'], 2),
                        (float) number_format((float) $inputParams['samedaycourier-parcel-length'], 2),
                        (float) number_format((float) $inputParams['samedaycourier-parcel-height'],2),
                        (float) number_format((float) $inputParams['samedaycourier-parcel-width'], 2)
                    ),
                    $inputParams['samedaycourier-parcel-observation'],
                    (bool) $inputParams['samedaycourier-parcel-is-last']
                )
            ),
        );

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
