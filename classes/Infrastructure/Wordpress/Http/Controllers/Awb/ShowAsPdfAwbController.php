<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Sameday;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwb;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwbRequest;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\WordpressSamedaySettingsProvider;

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
        $showAsPdfAwbItem = ShowAsPdfAwbItem::fromArray($inputParams);

        try {
            $samedayApiClient = new Sameday(SdkInitiator::init());
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

            return;
        }

        $showAsPdf = new ShowAsPdfAwb(
            new ShowAsPdfAwbRequest(
                $showAsPdfAwbItem,
                (new WordpressSamedaySettingsProvider())->get()->getDefaultLabelFormat(),
                new SamedayAwbRepository(),
                $samedayApiClient,
            )
        );
        $result = $showAsPdf->execute();

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
