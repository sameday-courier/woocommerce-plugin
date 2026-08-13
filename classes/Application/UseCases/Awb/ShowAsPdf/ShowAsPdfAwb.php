<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\Types\AwbPdfType;
use Sameday\Requests\SamedayGetAwbPdfRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;

final class ShowAsPdfAwb
{
    private ShowAsPdfAwbItem $showAsPdfAwbItem;

    private string $labelFormat;

    private SamedayAwbRepository $samedayAwbRepository;

    private Sameday $sameday;

    public function __construct(ShowAsPdfAwbRequest $showAsPdfAwbRequest)
    {
        $this->showAsPdfAwbItem = $showAsPdfAwbRequest->getShowAsPdfAwbItem();
        $this->labelFormat = $showAsPdfAwbRequest->getLabelFormat();
        $this->samedayAwbRepository = $showAsPdfAwbRequest->getSamedayAwbRepository();
        $this->sameday = $showAsPdfAwbRequest->getSameday();
    }

    /**
     * @return ShowAsPdfAwbResponse
     *
     * @throws SamedaySDKException
     */
    public function execute(): ShowAsPdfAwbResponse
    {
        $orderId = $this->showAsPdfAwbItem->getOrderId();
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb) {
            return new ShowAsPdfAwbResponse(
                $orderId,
                'AWB not found for this order.',
                ResponseNoticeType::ERROR,
            );
        }

        $pdf = null;
        $errorMessage = null;

        try {
            $content = $this->sameday->getAwbPdf(
                new SamedayGetAwbPdfRequest(
                    (string) $awb->getAwbNumber(),
                    new AwbPdfType($this->labelFormat)
                )
            );

            $pdf = $content->getPdf();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        if (null !== $errorMessage && (null === $pdf || '' === $pdf)) {
            return new ShowAsPdfAwbResponse(
                $orderId,
                $errorMessage,
                ResponseNoticeType::ERROR,
            );
        }

        return new ShowAsPdfAwbResponse(
            $orderId,
            null,
            ResponseNoticeType::SUCCESS,
            $pdf,
        );
    }
}
