<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate;

use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\Ports\SamedayShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\DTOs\OohDto;
use SamedayCourier\Shipping\Domain\DTOs\RecipientDto;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses\AwbGenerateRecipientResponse;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\ValueObject\Address\PostalCode;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;

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
     * @var SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser
     */
    private SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser;

    /**
     * @var SamedayServiceRules $samedayServiceRules
     */
    private SamedayServiceRules $samedayServiceRules;

    /**
     * @param GenerateAwbItem $awbItem
     * @param SamedayServiceRules $samedayServiceRules
     * @param SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser
     */
    public function __construct(
        GenerateAwbItem $awbItem,
        SamedayServiceRules $samedayServiceRules,
        SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser
    )
    {
        $this->awbItem = $awbItem;
        $this->samedayServiceRules = $samedayServiceRules;
        $this->samedayShippingHdAddressParser = $samedayShippingHdAddressParser;
    }

    /**
     * @return AwbGenerateRecipientResponse
     */
    public function resolve(): AwbGenerateRecipientResponse
    {
        $shipping = $this->awbItem->getShipping();
        $billing = $this->awbItem->getBilling();

        $city = $shipping->getCity() ?? $billing->getCity();
        $state = $shipping->getState() ?? $billing->getState();
        $country = $shipping->getCountry() ?? $billing->getCountry();
        $county = WooStateCodeResolver::resolveNameFromCode($country, $state) ?? '';
        $firstName = $shipping->getFirstName() ?? $billing->getFirstName();
        $lastName = $shipping->getLastName() ?? $billing->getLastName();
        $address_1 = $shipping->getAddress1() ?? $billing->getAddress1();
        $address_2 = $shipping->getAddress2() ?? $billing->getAddress2();
        $postalCode = PostalCode::tryCreate(
            $shipping->getPostcode() ?? $billing->getPostcode(),
            $state,
            $country
        )->getCode();
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

        $awbRecipient = new RecipientDto(
            $firstName,
            $lastName,
            $companyObject,
            $address_1,
            $address_2,
            $city,
            $county,
            $postalCode,
            $country,
            $email,
            $phone,
        );

        $service = $this->awbItem->getService();

        $lockerId = null;
        $oohLastMile = null;
        if ($this->isOohDeliveryType() && $this->hasLocker($locker = $this->awbItem->getLocker())) {
            if ($service->getSamedayCode() === SamedayConstants::LOCKER_NEXT_DAY_CODE) {
                $lockerId = $locker->getLockerId();
            }

            if ($service->getSamedayCode() === SamedayConstants::PUDO_CODE) {
                $oohLastMile = $locker->getLockerId();
            }

            // Overwrite recipient data with OOH data for the delivery
            $awbRecipient->setCity($locker->getCity());
            $awbRecipient->setCounty($locker->getCounty());
            $awbRecipient->setAddress($locker->getAddress());
            $awbRecipient->setPostalCode($locker->getPostalCode());
        }

        $post_meta_samedaycourier_address_hd = $this->samedayShippingHdAddressParser->parse(
            $this->awbItem->getOrderId()
        );

        if (!$this->isOohDeliveryType() && $this->isHomeDeliveryType()) {
            $awbRecipient->setCity($post_meta_samedaycourier_address_hd['city']);
            $county = WooStateCodeResolver::resolveNameFromCode(
                $post_meta_samedaycourier_address_hd['country'],
                $post_meta_samedaycourier_address_hd['state']
            );
            $awbRecipient->setCounty($county);
            $awbRecipient->setAddress1($post_meta_samedaycourier_address_hd['address_1']);
            $awbRecipient->setAddress2($post_meta_samedaycourier_address_hd['address_2']);
            $awbRecipient->setPostalCode($post_meta_samedaycourier_address_hd['postcode']);
        }

        $ooh = new OohDto(
            $lockerId,
            $oohLastMile
        );

        $currency = SamedayConstants::CURRENCY_MAPPER[$country];

        return new AwbGenerateRecipientResponse(
            $awbRecipient,
            $ooh,
            $currency
        );
    }

    /**
     * @param LockerDto|null $locker
     *
     * @return bool
     */
    private function hasLocker(?LockerDto $locker): bool
    {
        return $locker !== null;
    }

    /**
     * @return bool
     */
    private function isOohDeliveryType(): bool
    {
        return $this->samedayServiceRules->isOohDeliveryOption($this->awbItem->getService());
    }

    /**
     * @return bool
     */
    private function isHomeDeliveryType(): bool
    {
        return null !== $this->samedayShippingHdAddressParser->parse(
            $this->awbItem->getOrderId()
        );
    }
}
