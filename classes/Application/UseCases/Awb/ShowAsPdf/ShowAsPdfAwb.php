<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Objects\Types\AwbPdfType;
use Sameday\Requests\SamedayGetAwbPdfRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayAwbRepository;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;

if (!defined('ABSPATH')) {
    exit;
}

final class ShowAsPdfAwb
{
    /**
     * @var ShowAsPdfAwbRequest $showAsPdfAwbRequest
     */
    private ShowAsPdfAwbRequest $showAsPdfAwbRequest;

    /**
     * @var SamedayAwbRepository $samedayAwbRepository
     */
    private SamedayAwbRepository $samedayAwbRepository;

    /**
     * @param ShowAsPdfAwbRequest $showAsPdfAwbRequest
     */
    public function __construct(ShowAsPdfAwbRequest $showAsPdfAwbRequest)
    {
        $this->showAsPdfAwbRequest = $showAsPdfAwbRequest;
        $this->samedayAwbRepository = new SamedayAwbRepository();
    }

    /**
     * @return ShowAsPdfAwbResponse
     *
     * @throws SamedaySDKException
     */
    public function execute(): ShowAsPdfAwbResponse
    {
        $orderId = $this->showAsPdfAwbRequest->getOrderId();
        $awb = $this->samedayAwbRepository->getAwbForOrderId($orderId);

        if (null === $awb) {
            return new ShowAsPdfAwbResponse(
                $orderId,
                ResponseNoticeType::ERROR,
                'AWB not found for this order.',
            );
        }

        $sameday = new Sameday(SdkInitiator::init());
        $pdf = null;
        $errorMessage = null;

        try {
            $content = $sameday->getAwbPdf(
                new SamedayGetAwbPdfRequest(
                    (string) $awb->getAwbNumber(),
                    new AwbPdfType($this->showAsPdfAwbRequest->getLabelFormat())
                )
            );

            $pdf = $content->getPdf();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        if (null !== $errorMessage && (null === $pdf || '' === $pdf)) {
            return new ShowAsPdfAwbResponse(
                $orderId,
                ResponseNoticeType::ERROR,
                $errorMessage,
            );
        }

        return new ShowAsPdfAwbResponse(
            $orderId,
            ResponseNoticeType::SUCCESS,
            null,
            $pdf,
        );
    }
}
