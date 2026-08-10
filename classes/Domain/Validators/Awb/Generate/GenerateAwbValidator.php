<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Validators\Awb\Generate;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbValidator
{
    public function validate(GenerateAwbValidatorRequest $request): GenerateAwbValidatorResponse
    {
        $billing = $request->getBilling();
        $service = $request->getSamedayService();

        $response = new GenerateAwbValidatorResponse();
        if (null === $service) {
            $response->setErrors(
                'service',
                "Selected service could not be found."
            );
        }

        if (null === $billing->getPhone()) {
            $response->setErrors(
                'phone',
                "Must complete phone number."
            );
        }

        if (null === $billing->getEmail()) {
            $response->setErrors(
                'email',
                "Must complete email."
            );
        }

        if (empty($request->getShippingLines())) {
            $response->setErrors(
                "shipping_lines",
                "No shipping lines for this awb item."
            );
        }

        return $response;
    }
}
