<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers;

use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\Types\AwbPdfType;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;

if (!defined('ABSPATH')) {
    exit;
}

final class ShowAsPdfAwbController extends AbstractController
{
    private const ACTION = 'show-as-pdf';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array<string, mixed> $inputParams
     *
     * @return void
     *
     * @throws SamedaySDKException
     */
    protected function processPostAction(array $inputParams): void
    {
        $showAsPdf = new ShowAsPdfAwb(
            new ShowAsPdfAwbRequest(
                (int) $inputParams['order-id'],
                OptionsHandler::getSamedayOptions()['default_label_format'] ?? AwbPdfType::A4
            )
        );
        $result = $showAsPdf->execute();

        if ($result->hasPdf()) {
            header('Content-type: application/pdf');
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');

            echo $result->getPdf();

            exit;
        }

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                'show_awb_pdf_notice',
                $result->getNoticeMessage(),
                $result->getNoticeType(),
                true
            );
        }

        Redirector::to(
            'post.php',
            [
                'post' => $result->getOrderId(),
                'action' => 'edit',
                'show-awb' => $result->getNoticeType(),
            ]
        );
    }
}
