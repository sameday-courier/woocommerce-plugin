<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use Exception;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwb
{
    /**
     * @var GenerateAwbRequest $generateAwbRequest
     */
    private GenerateAwbRequest $generateAwbRequest;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    public function __construct(
        GenerateAwbRequest $generateAwbRequest
    )
    {
        $this->generateAwbRequest = $generateAwbRequest;
        $this->sameday = $generateAwbRequest->sameday;
    }

    /**
     * @return GenerateAwbResponse
     */
    public function execute(): GenerateAwbResponse
    {
        $generateAwbItem = $this->generateAwbRequest->generateAwbItem;

        $postAwbRequest = new SamedayPostAwbRequest(
            $generateAwbItem->getPickupPointId(),
            $generateAwbItem->getContactPersonId(),
            $generateAwbItem->getPackageType(),
            $generateAwbItem->getParcelsDimensions(),
            $generateAwbItem->getServiceId(),
            $generateAwbItem->getAwbPayment(),
            $generateAwbItem->getAwbRecipient(),
            $generateAwbItem->getInsuredValue(),
            $generateAwbItem->getCashOnDeliveryAmount(),
            $generateAwbItem->getCashOnDeliveryCollector(),
            $generateAwbItem->getThirdPartyPickup(),
            $generateAwbItem->getServiceTaxIds(),
            $generateAwbItem->getDeliveryIntervalServiceType(),
            $generateAwbItem->getReference(),
            $generateAwbItem->getObservation(),
            $generateAwbItem->getPriceObservation(),
            $generateAwbItem->getClientObservation(),
            $generateAwbItem->getLockerFirstMile(),
            $generateAwbItem->getLockerLastMile(),
            $generateAwbItem->getOohFirstMile(),
            $generateAwbItem->getOohLastMile(),
            $generateAwbItem->getCurrency()
        );

        try {
            $this->sameday->postAwb($postAwbRequest);
        } catch (Exception $exception) {
            return new GenerateAwbResponse(
                ResponseNoticeType::ERROR,
                $exception->getMessage(),
            );
        }

        return new GenerateAwbResponse(
            ResponseNoticeType::SUCCESS,
            "Awb generated successfully.",
        );
    }
}
