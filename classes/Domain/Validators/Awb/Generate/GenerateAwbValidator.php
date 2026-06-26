<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Validators\Awb\Generate;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbResponse;
use SamedayCourier\Shipping\Domain\SamedayConstants;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbValidator
{
    /**
     * @var GenerateAwbValidatorRequest
     */
    private GenerateAwbValidatorRequest $request;

    public function __construct(GenerateAwbValidatorRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return GenerateAwbValidatorResponse
     */
    public function validate(): GenerateAwbValidatorResponse
    {
        $awbItem = $this->request->awbItem;
        $service = $this->request->samedayService;

        $response = new GenerateAwbValidatorResponse();
        if (null === $service) {
            $response->setErrors(
                'service',
                "Selected service could not be found."
            );
        }

        if (null === $awbItem->getBilling()->getPhone()) {
            $response->setErrors(
                'phone',
                "Must complete phone number."
            );
        }

        if (null === $awbItem->getBilling()->getEmail()) {
            $response->setErrors(
                'email',
                "Must complete email."
            );
        }

        if (empty($this->request->awbItem->getShippingLines())) {
            $response->setErrors(
                "shipping_lines",
                "No shipping lines for this awb item."
            );
        }

        return $response;
    }
}
