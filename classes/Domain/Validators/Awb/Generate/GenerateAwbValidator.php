<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Validators\Awb\Generate;

final class GenerateAwbValidator
{
    public function validate(GenerateAwbValidatorRequest $request): GenerateAwbValidatorResponse
    {
        $billing = $request->getBilling();
        $service = $request->getSamedayService();
        $pickupPoint = $request->getPickupPoint();

        $response = new GenerateAwbValidatorResponse();

        if ($request->hasExistingAwb()) {
            $response->setErrors(
                'awb',
                sprintf('Order #%d already has an AWB.', $request->getOrderId())
            );
        }

        if (null === $service) {
            $response->setErrors(
                'service',
                'Selected service could not be found.'
            );
        }

        if (null === $pickupPoint) {
            $response->setErrors(
                'pickup_point',
                'Selected pickup point could not be found.'
            );
        }

        if (null === $billing->getPhone()) {
            $response->setErrors(
                'phone',
                'Must complete phone number.'
            );
        }

        if (null === $billing->getEmail()) {
            $response->setErrors(
                'email',
                'Must complete email.'
            );
        }

        if (empty($request->getShippingLines())) {
            $response->setErrors(
                'shipping_lines',
                'No shipping lines for this awb item.'
            );
        }

        if (!$request->hasParcels()) {
            $response->setErrors(
                'parcels',
                'At least one parcel is required.'
            );
        }

        return $response;
    }
}
