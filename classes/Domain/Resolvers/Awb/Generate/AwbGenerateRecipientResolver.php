<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate;

use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class AwbGenerateRecipientResolver
{
    /**
     * @var GenerateAwbItem $awbItem
     */
    private GenerateAwbItem $awbItem;

    /**
     * @param GenerateAwbItem $awbItem
     */
    public function __construct(
        GenerateAwbItem $awbItem
    )
    {
        $this->awbItem = $awbItem;
    }

    /**
     * @return AwbRecipientEntityObject
     */
    public function resolve(): AwbRecipientEntityObject
    {
        $shipping = $this->awbItem->getShipping();
        $billing = $this->awbItem->getBilling();

        $city = $shipping->getCity() ?? $billing->getCity();
        $state = $shipping->getState() ?? $billing->getState();
        $country = $shipping->getCountry() ?? $billing->getCountry();
        $firstName = $shipping->getFirstName() ?? $billing->getFirstName();
        $lastName = $shipping->getLastName() ?? $billing->getLastName();
        $address_1 = $shipping->getAddress1() ?? $billing->getAddress1();
        $address_2 = $shipping->getAddress2() ?? $billing->getAddress2();
        $postalCode = $shipping->getPostcode() ?? $billing->getPostcode();
        $phone = $shipping->getPhone() ?? $billing->getPhone();
        $email = $shipping->getEmail() ?? $billing->getEmail();
        $companyObject = null;
        $company = $shipping->getCompany() ?? $billing->getCompany();
        if ('' !== ($shipping->getCompany() ?? '')) {
            $companyObject = new CompanyEntityObject(
                $company,
                ...['', '', '', '']
            );
        }

        if (false === Helper::validatePostalCode($postalCode, $state)) {
            $postalCode = null;
        }

        $county = Helper::convertStateCodeToName(
            $country,
            $state
        );

        $address = sprintf(
            '%s %s',
            ltrim($address_1),
            ltrim($address_2)
        );

        $name = sprintf(
            '%s %s',
            ltrim($firstName),
            ltrim($lastName)
        );

        return new AwbRecipientEntityObject(
            $city,
            $county,
            $address,
            $name,
            $phone,
            $email,
            $companyObject,
            $postalCode
        );
    }
}


