<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate;

use SamedayCourier\Shipping\Domain\DTOs\BillingObject;
use SamedayCourier\Shipping\Domain\DTOs\ShippingObject;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbRecipientRules
{
    public static function validate(
        ShippingObject $shipping,
        BillingObject $billing
    ): ValidationResult
    {
        if (self::isShippingDetailsComplete($shipping)) {
            return self::validateContactDetails($billing);
        }

        if (self::isBillingDetailsComplete($billing)) {
            return new ValidationResult();
        }

        return self::validateBillingDetails($billing);
    }

    private static function isShippingDetailsComplete(ShippingObject $shipping): bool
    {
        return !self::isEmpty($shipping->getFirstName())
            && !self::isEmpty($shipping->getLastName())
            && !self::isEmpty($shipping->getAddress1())
            && !self::isEmpty($shipping->getCity())
            && !self::isEmpty($shipping->getState())
            && !self::isEmpty($shipping->getCountry());
    }

    private static function isBillingDetailsComplete(BillingObject $billing): bool
    {
        return self::isAddressComplete(
            $billing->getFirstName(),
            $billing->getLastName(),
            $billing->getAddress1(),
            $billing->getCity(),
            $billing->getState(),
            $billing->getCountry()
        )
            && !self::isEmpty($billing->getPhone())
            && !self::isEmpty($billing->getEmail());
    }

    private static function validateContactDetails(BillingObject $billing): ValidationResult
    {
        return self::validateBillingDetails($billing, false);
    }

    private static function validateBillingDetails(
        BillingObject $billing,
        bool $includeAddress = true
    ): ValidationResult {
        $errors = [];

        if ($includeAddress) {
            if (self::isEmpty($billing->getFirstName()) || self::isEmpty($billing->getLastName())) {
                $errors[] = 'Must complete recipient name!';
            }

            if (self::isEmpty($billing->getAddress1())) {
                $errors[] = 'Must complete address!';
            }

            if (self::isEmpty($billing->getCity())) {
                $errors[] = 'Must complete city!';
            }

            if (self::isEmpty($billing->getState())) {
                $errors[] = 'Must complete state!';
            }

            if (self::isEmpty($billing->getCountry())) {
                $errors[] = 'Must complete country!';
            }
        }

        if (self::isEmpty($billing->getPhone())) {
            $errors[] = 'Must complete phone number!';
        }

        if (self::isEmpty($billing->getEmail())) {
            $errors[] = 'Must complete email!';
        }

        return new ValidationResult($errors);
    }

    private static function isAddressComplete(
        ?string $firstName,
        ?string $lastName,
        ?string $address1,
        ?string $city,
        ?string $state,
        ?string $country
    ): bool {
        return !self::isEmpty($firstName)
            && !self::isEmpty($lastName)
            && !self::isEmpty($address1)
            && !self::isEmpty($city)
            && !self::isEmpty($state)
            && !self::isEmpty($country);
    }

    private static function isEmpty(?string $value): bool
    {
        return null === $value || '' === $value;
    }
}
