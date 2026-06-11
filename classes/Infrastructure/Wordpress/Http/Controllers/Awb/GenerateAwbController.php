<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

if (!defined("ABSPATH")) {
    exit;
}

final class GenerateAwbController extends AbstractController
{
    private CONST ACTION = "add-awb";

    public function getAction(): string
    {
        return self::ACTION;
    }

    protected function processPostAction(array $inputParams): void
    {
        $orderDetails = wc_get_order($inputParams['samedaycourier-order-id']);

        if (empty($orderDetails)) {
            Redirector::to('index.php');
        }

        $data = array_merge($inputParams, $orderDetails->get_data());

        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                ResponseNoticeType::ERROR,
                $exception->getMessage()
            );

            Redirector::to('index.php');
        }

        $generateAwbRequest = new GenerateAwbRequest(
            new GenerateAwbItem(
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ),
            $samedayApiClient,
        );

        $awbGenerate = new GenerateAwb($generateAwbRequest);

        $result = $awbGenerate->execute();

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                ResponseNoticeType::SUCCESS,
                "Awb generated successfully.",
            );
        }

        Redirector::to('index.php');
    }
}
