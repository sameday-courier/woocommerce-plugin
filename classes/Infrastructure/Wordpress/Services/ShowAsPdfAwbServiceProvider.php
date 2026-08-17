<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\Types\AwbPdfType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowAsPdfAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowAsPdfRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\ShowAsPdfAwbResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\ShowAsPdfAwbServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderAwbProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayAwbRepository;

final class ShowAsPdfAwbServiceProvider implements ShowAsPdfAwbServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    private OrderAwbProviderInterface $orderAwbProvider;

    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    public function __construct(
        ?CourierServiceProviderInterface $courier = null,
        ?OrderAwbProviderInterface $orderAwbProvider = null,
        ?CarrierSettingsProviderInterface $carrierSettingsProvider = null
    ) {
        $this->courier = $courier ?? new CourierServiceProvider();
        $this->orderAwbProvider = $orderAwbProvider ?? new WooOrderAwbProvider(new SamedayAwbRepository());
        $this->carrierSettingsProvider = $carrierSettingsProvider ?? new CarrierSettingsServiceProvider();
    }

    /**
     * @param ShowAsPdfAwbRequestDto $showAsPdfAwbRequestDto
     *
     * @return ShowAsPdfAwbResponseDto
     */
    public function showAsPdf(ShowAsPdfAwbRequestDto $showAsPdfAwbRequestDto): ShowAsPdfAwbResponseDto
    {
        $orderId = $showAsPdfAwbRequestDto->getOrderId();
        $awb = $this->orderAwbProvider->get($orderId);

        if (null === $awb) {
            return new ShowAsPdfAwbResponseDto(
                $orderId,
                false,
                'AWB not found for this order.'
            );
        }

        try {
            $pdfResponse = $this->courier->showAsPdf(
                new ShowAsPdfRequestDto(
                    (string) $awb->getAwbNumber(),
                    new AwbPdfType($this->carrierSettingsProvider->get()->getDefaultLabelFormat())
                )
            );
        } catch (CourierServiceException $exception) {
            return new ShowAsPdfAwbResponseDto(
                $orderId,
                false,
                $exception->getMessage()
            );
        }

        return new ShowAsPdfAwbResponseDto(
            $orderId,
            true,
            'AWB PDF generated successfully.',
            $pdfResponse->getPdf()
        );
    }
}
