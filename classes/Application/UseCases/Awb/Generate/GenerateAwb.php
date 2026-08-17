<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Generate;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GenerateAwbServiceRequestDto;
use SamedayCourier\Shipping\Domain\Ports\GenerateAwbServiceProviderInterface;

final class GenerateAwb
{
    private GenerateAwbItem $awbItem;

    private GenerateAwbServiceProviderInterface $generateAwbServiceProvider;

    public function __construct(GenerateAwbRequest $generateAwbRequest)
    {
        $this->awbItem = $generateAwbRequest->getGenerateAwbItem();
        $this->generateAwbServiceProvider = $generateAwbRequest->getGenerateAwbServiceProvider();
    }

    public function execute(): GenerateAwbResponse
    {
        $generateAwbResponse = $this->generateAwbServiceProvider->generate(
            new GenerateAwbServiceRequestDto(
                $this->awbItem->getOrderId(),
                $this->awbItem->getServiceId(),
                $this->awbItem->getPickupPointId(),
                $this->awbItem->getShippingLines(),
                $this->awbItem->getShipping(),
                $this->awbItem->getBilling(),
                $this->awbItem->getLocker(),
                $this->awbItem->hasOpenPackage(),
                $this->awbItem->hasLockerFirstMile(),
                $this->awbItem->getPackageType(),
                $this->awbItem->getAwbPayment(),
                $this->awbItem->getInsuranceValue(),
                $this->awbItem->getRepayment(),
                $this->awbItem->getClientReference(),
                $this->awbItem->getObservation(),
                $this->awbItem->getPackageDimensions()
            )
        );

        return new GenerateAwbResponse(
            $generateAwbResponse->getMessage(),
            $generateAwbResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
