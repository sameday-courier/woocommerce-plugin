<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

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
     */
    protected function processAction(array $inputParams): void
    {
        $showAsPdfAwbItem = ShowAsPdfAwbItem::fromArray($inputParams);

        try {
            $showAsPdf = new ShowAsPdfAwb(
                new ShowAsPdfAwbRequest(
                    $showAsPdfAwbItem,
                    (new CarrierSettingsServiceProvider())->get()->getDefaultLabelFormat(),
                    new SamedayAwbRepository(),
                    new CourierServiceProvider(),
                )
            );
            $result = $showAsPdf->execute();
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                $exception->getMessage(),
            );

            $this->redirectTo(
                'post.php',
                [
                    'post' => $showAsPdfAwbItem->getOrderId(),
                    'action' => 'edit',
                ]
            );
        }

        if ($result->hasPdf()) {
            $pdf = $result->getPdf();

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            nocache_headers();
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="awb-' . $result->getOrderId() . '.pdf"');
            header('Content-Length: ' . strlen($pdf));

            echo $pdf;

            exit;
        }

        if ($result->hasNotices()) {
            NoticerHandler::addFlashNotice(
                $result->getNoticeMessage(),
                $result->getNoticeType(),
            );
        }

        $this->redirectTo(
            'post.php',
            [
                'post' => $result->getOrderId(),
                'action' => 'edit',
                'show-awb' => $result->getNoticeType(),
            ]
        );
    }
}
