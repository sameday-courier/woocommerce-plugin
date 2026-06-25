<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate\Resolvers;

use JsonException;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use SamedayCourier\Shipping\Domain\Awb\Generate\AwbRecipient;
use SamedayCourier\Shipping\Domain\Awb\Generate\GenerateAwbContext;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

final class GenerateAwbRecipientResolver
{
    /**
     * @throws JsonException
     */
    public function resolve(GenerateAwbContext $context, SamedayService $service): AwbRecipient
    {
        $shipping = $context->getShipping();
        $billing = $context->getBilling();

        $city = $this->resolveField($shipping->getCity(), $billing->getCity());
        $state = $this->resolveField($shipping->getState(), $billing->getState());
        $country = $this->resolveField($shipping->getCountry(), $billing->getCountry());
        $postalCode = $this->resolveField($shipping->getPostcode(), $billing->getPostcode());

        if (false === Helper::validatePostalCode($postalCode, $state)) {
            $postalCode = null;
        }

        $county = Helper::convertStateCodeToName($country, $state);

        $address = sprintf(
            '%s %s',
            ltrim($shipping->getAddress1() ?? ''),
            ltrim($shipping->getAddress2() ?? '')
        );

        $address1 = $shipping->getAddress1();
        $address2 = $shipping->getAddress2();

        $name = sprintf(
            '%s %s',
            ltrim($shipping->getFirstName() ?? ''),
            ltrim($shipping->getLastName() ?? '')
        );

        $phone = $billing->getPhone() ?? '';
        $email = $billing->getEmail() ?? '';

        if (null !== ($locker = $context->getLocker())
            && Helper::isOohDeliveryOption($service->getSamedayCode())
        ) {
            $locker = json_decode(
                $locker,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $city = $locker['city'] ?? $city;
            $county = $locker['county'] ?? $county;
            $address = $locker['address'] ?? $address;
            $postalCode = $locker['postalCode'] ?? $postalCode;
            $address1 = $address;
            $address2 = $locker['name'];
            $state = Helper::convertStateNameToCode($country, $county);
        }

        $homeDeliveryAddress = $context->getHomeDeliveryAddress();

        if (!Helper::isOohDeliveryOption($service->getSamedayCode())) {
            if (null !== $homeDeliveryAddress) {
                $city = $homeDeliveryAddress['city'];
                $county = Helper::convertStateCodeToName(
                    $homeDeliveryAddress['country'],
                    $homeDeliveryAddress['state']
                );
                $address = sprintf(
                    '%s %s',
                    $homeDeliveryAddress['address_1'],
                    $homeDeliveryAddress['address_2']
                );
                $postalCode = $homeDeliveryAddress['postcode'];
                $address1 = $homeDeliveryAddress['address_1'];
                $address2 = $homeDeliveryAddress['address_2'];
                $state = $homeDeliveryAddress['state'];
            } else {
                $city = $billing->getCity();
                $address1 = $billing->getAddress1();
                $address2 = $billing->getAddress2();
                $address = sprintf(
                    '%s %s',
                    $address1,
                    $address2
                );
                $country = $billing->getCountry();
                $state = $billing->getState();
                $county = Helper::convertStateCodeToName($country, $state);
                $postalCode = $billing->getPostcode();
            }
        }

        $company = $this->resolveCompany($shipping->getCompany());

        return new AwbRecipient(
            $city,
            $county,
            $address,
            $name,
            $phone,
            $email,
            $postalCode,
            $address1,
            $address2,
            $state,
            $country,
            $company
        );
    }

    private function resolveCompany(?string $companyName): ?CompanyEntityObject
    {
        if ('' === ($companyName ?? '')) {
            return null;
        }

        return new CompanyEntityObject(
            $companyName,
            '',
            '',
            '',
            ''
        );
    }

    /**
     * @param string|null $primary
     * @param string|null $fallback
     *
     * @return string|null
     */
    private function resolveField(?string $primary, ?string $fallback): ?string
    {
        if (null === $primary || '' === $primary) {
            return $fallback;
        }

        return $primary;
    }
}
