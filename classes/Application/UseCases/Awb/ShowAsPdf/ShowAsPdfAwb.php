<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use Sameday\Objects\Types\AwbPdfType;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowAsPdfRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class ShowAsPdfAwb
{
    private ShowAsPdfAwbItem $showAsPdfAwbItem;

    private string $labelFormat;

    private SamedayAwbRepository $samedayAwbRepository;

    private CourierServiceProviderInterface $courier;

    public function __construct(ShowAsPdfAwbRequest $showAsPdfAwbRequest)
    {
        $this->showAsPdfAwbItem = $showAsPdfAwbRequest->getShowAsPdfAwbItem();
        $this->labelFormat = $showAsPdfAwbRequest->getLabelFormat();
        $this->samedayAwbRepository = $showAsPdfAwbRequest->getSamedayAwbRepository();
        $this->courier = $showAsPdfAwbRequest->getCourier();
    }

    /**
     * @return ShowAsPdfAwbResponse
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

        try {
            $pdfResponse = $this->courier->showAsPdf(
                new ShowAsPdfRequestDto(
                    (string) $awb->getAwbNumber(),
                    new AwbPdfType($this->labelFormat)
                )
            );
        } catch (CourierServiceException $exception) {
            return new ShowAsPdfAwbResponse(
                $orderId,
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new ShowAsPdfAwbResponse(
            $orderId,
            null,
            ResponseNoticeType::SUCCESS,
            $pdfResponse->getPdf(),
        );
    }
}
