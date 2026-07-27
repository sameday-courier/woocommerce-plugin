<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Sameday\Exceptions\SamedaySDKException;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Domain\SamedaySettings;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
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
    protected function processAction(array $inputParams): void
    {
        $showAsPdf = new ShowAsPdfAwb(
            new ShowAsPdfAwbRequest(
                (int) $inputParams['order-id'],
                SamedaySettings::getDefaultLabelFormat()
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
                $result->getNoticeMessage(),
                $result->getNoticeType(),
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
