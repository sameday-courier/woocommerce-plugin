<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate;

use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayServiceRepository;
use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwbItem;
use SamedayCourier\Shipping\Domain\DTOs\OohDto;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses\AwbGenerateRecipientResponse;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
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
     * @return AwbGenerateRecipientResponse
     */
    public function resolve(): AwbGenerateRecipientResponse
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

        $awbRecipient = new AwbRecipientEntityObject(
            $city,
            $county,
            $address,
            $name,
            $phone,
            $email,
            $companyObject,
            $postalCode
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

        $post_meta_samedaycourier_address_hd = Helper::parsePostMetaSamedaycourierAddressHd($this->awbItem->getOrderId());

        if (!$this->isOohDeliveryType() && $this->isHomeDeliveryType()) {
            $awbRecipient->setCity($post_meta_samedaycourier_address_hd['city']);
            $county = Helper::convertStateCodeToName(
                $post_meta_samedaycourier_address_hd['country'],
                $post_meta_samedaycourier_address_hd['state']
            );
            $awbRecipient->setCounty($county);
            $address = sprintf(
                '%s %s',
                $post_meta_samedaycourier_address_hd['address_1'],
                $post_meta_samedaycourier_address_hd['address_2']
            );
            $awbRecipient->setAddress($address);
            $awbRecipient->setPostalCode($post_meta_samedaycourier_address_hd['postcode']);
        }

        $ooh = new OohDto(
            $lockerId,
            $oohLastMile
        );

        return new AwbGenerateRecipientResponse(
            $ooh,
            $awbRecipient
        );
    }

    /**
     * @param string|null $locker
     *
     * @return bool
     */
    private function hasLocker(?string $locker): bool
    {
        return $locker !== null;
    }

    /**
     * @return bool
     */
    private function isOohDeliveryType(): bool
    {
        return (new SamedayServiceRules(new SamedayServiceRepository()))->isOohDeliveryOption($this->awbItem->getService());
    }

    /**
     * @return bool
     */
    private function isHomeDeliveryType(): bool
    {
        $post_meta_samedaycourier_address_hd = Helper::parsePostMetaSamedaycourierAddressHd(
            $this->awbItem->getOrderId()
        );

        return $post_meta_samedaycourier_address_hd !== null;
    }
}


