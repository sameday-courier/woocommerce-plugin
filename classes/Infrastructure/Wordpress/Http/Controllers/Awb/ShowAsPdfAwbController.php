<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Awb;

use Exception;
use SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf\ShowAsPdfAwbRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\Admin\NoticerHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\TranslatorHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\ShowAsPdfAwbFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers\ShowAsPdfAwbMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\ResponseNoticeType\ResponseNoticeType;

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
     * @param array $inputParams
     *
     * @return void
     */
    protected function processAction(array $inputParams): void
    {
        $params = new ShowAsPdfAwbMapper($inputParams);
        $orderId = $params->orderId();
        $showAsPdfAwb = ShowAsPdfAwbFactory::create();
        $result = null;

        try {
            $result = $showAsPdfAwb->execute(
                new ShowAsPdfAwbRequest($orderId)
            );
        } catch (Exception $exception) {
            NoticerHandler::addFlashNotice(
                $exception->getMessage(),
            );

            $this->redirectTo(
                'post.php',
                [
                    'post' => $orderId,
                    'action' => 'edit',
                ]
            );
        }

        if (null === $result) {
            return;
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

        $noticeType = $result->hasError()
            ? ResponseNoticeType::ERROR
            : ResponseNoticeType::SUCCESS;

        if ('' !== $result->getNoticeMessage()) {
            NoticerHandler::addFlashNotice(
                TranslatorHandler::translate($result->getNoticeMessage()),
                $noticeType,
            );
        }

        $this->redirectTo(
            'post.php',
            [
                'post' => $result->getOrderId(),
                'action' => 'edit',
                'show-awb' => $noticeType,
            ]
        );
    }
}
