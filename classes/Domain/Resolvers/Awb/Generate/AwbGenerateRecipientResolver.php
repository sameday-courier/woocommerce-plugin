<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate;

use SamedayCourier\Shipping\Domain\DTOs\BillingDto;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\DTOs\ShippingDto;
use SamedayCourier\Shipping\Domain\Models\CarrierService;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CarrierShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\DTOs\OohDto;
use SamedayCourier\Shipping\Domain\DTOs\RecipientDto;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses\AwbGenerateRecipientResponse;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;
use SamedayCourier\Shipping\Domain\ValueObject\Address\PostalCode;

class AwbGenerateRecipientResolver
{
    /**
     * @var CarrierServiceRules $carrierServiceRules
     */
    private CarrierServiceRules $carrierServiceRules;

    /**
     * @var CarrierShippingHdAddressParserInterface $samedayShippingHdAddressParser
     */
    private CarrierShippingHdAddressParserInterface $samedayShippingHdAddressParser;

    /**
     * @var StateCodeResolverInterface $stateCodeResolver
     */
    private StateCodeResolverInterface $stateCodeResolver;

    /**
     * @var CityPostalCodeProviderInterface
     */
    private CityPostalCodeProviderInterface $cityPostalCodeProvider;

    /**
     * @param CarrierServiceRules $carrierServiceRules
     * @param CarrierShippingHdAddressParserInterface $samedayShippingHdAddressParser
     * @param StateCodeResolverInterface $stateCodeResolver
     * @param CityPostalCodeProviderInterface $cityPostalCodeProvider
     */
    public function __construct(
        CarrierServiceRules $carrierServiceRules,
        CarrierShippingHdAddressParserInterface $samedayShippingHdAddressParser,
        StateCodeResolverInterface $stateCodeResolver,
        CityPostalCodeProviderInterface $cityPostalCodeProvider
    )
    {
        $this->carrierServiceRules = $carrierServiceRules;
        $this->samedayShippingHdAddressParser = $samedayShippingHdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
        $this->cityPostalCodeProvider = $cityPostalCodeProvider;
    }

    /**
     * @param int $orderId
     * @param ShippingDto $shipping
     * @param BillingDto $billing
     * @param CarrierService $service
     * @param LockerDto|null $locker
     *
     * @return AwbGenerateRecipientResponse
     */
    public function resolve(
        int $orderId,
        ShippingDto $shipping,
        BillingDto $billing,
        CarrierService $service,
        ?LockerDto $locker
    ): AwbGenerateRecipientResponse
    {
        $city = $shipping->getCity() ?? $billing->getCity();
        $state = $shipping->getState() ?? $billing->getState();
        $country = $shipping->getCountry() ?? $billing->getCountry();
        $county = $this->stateCodeResolver->resolveNameFromCode($country, $state) ?? '';
        $firstName = $shipping->getFirstName() ?? $billing->getFirstName();
        $lastName = $shipping->getLastName() ?? $billing->getLastName();
        $address_1 = $shipping->getAddress1() ?? $billing->getAddress1();
        $address_2 = $shipping->getAddress2() ?? $billing->getAddress2();
        $postalCode = PostalCode::tryCreate(
            $shipping->getPostcode() ?? $billing->getPostcode(),
            (string) ($state ?? ''),
            (string) ($country ?? ''),
            $this->cityPostalCodeProvider
        )->getCode();
        $phone = $shipping->getPhone() ?? $billing->getPhone();
        $email = $shipping->getEmail() ?? $billing->getEmail();
        $company = $shipping->getCompany() ?? $billing->getCompany();
        if ('' === ($company ?? '')) {
            $company = null;
        }

        $awbRecipient = new RecipientDto(
            $firstName,
            $lastName,
            $company,
            $address_1,
            $address_2,
            $city,
            $county,
            $postalCode,
            $country,
            $email,
            $phone,
        );

        $lockerId = null;
        $oohLastMile = null;
        if ($this->isOohDeliveryType($service) && $this->hasLocker($locker)) {
            if ($this->isEasyBoxServiceType($service)) {
                $lockerId = $locker->getLockerId();
            }

            if ($this->isPudoServiceType($service)) {
                $oohLastMile = $locker->getLockerId();
            }

            $awbRecipient->setCity($locker->getCity());
            $awbRecipient->setCounty($locker->getCounty());
            $awbRecipient->setAddress($locker->getAddress());
            $awbRecipient->setPostalCode($locker->getPostalCode());
        }

        $post_meta_samedaycourier_address_hd = $this->samedayShippingHdAddressParser->parse($orderId);

        if (!$this->isOohDeliveryType($service) && $this->isHomeDeliveryType($orderId)) {
            $awbRecipient->setCity(
                isset($post_meta_samedaycourier_address_hd['city'])
                    ? (string) $post_meta_samedaycourier_address_hd['city']
                    : null
            );
            $county = $this->stateCodeResolver->resolveNameFromCode(
                (string) ($post_meta_samedaycourier_address_hd['country'] ?? ''),
                (string) ($post_meta_samedaycourier_address_hd['state'] ?? '')
            );
            $awbRecipient->setCounty($county);
            $awbRecipient->setAddress1(
                isset($post_meta_samedaycourier_address_hd['address_1'])
                    ? (string) $post_meta_samedaycourier_address_hd['address_1']
                    : null
            );
            $awbRecipient->setAddress2(
                isset($post_meta_samedaycourier_address_hd['address_2'])
                    ? (string) $post_meta_samedaycourier_address_hd['address_2']
                    : null
            );
            $awbRecipient->setPostalCode(
                isset($post_meta_samedaycourier_address_hd['postcode'])
                    ? (string) $post_meta_samedaycourier_address_hd['postcode']
                    : null
            );
        }

        $ooh = new OohDto(
            $lockerId,
            $oohLastMile
        );

        $currency = CarrierConstants::CURRENCY_MAPPER[$country];

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
     * @param CarrierService $carrierService
     *
     * @return bool
     */
    private function isEasyBoxServiceType(CarrierService $carrierService): bool
    {
        return $this->carrierServiceRules->isEasyBoxServiceType($carrierService);
    }

    /**
     * @param CarrierService $carrierService
     *
     * @return bool
     */
    private function isPudoServiceType(CarrierService $carrierService): bool
    {
        return $this->carrierServiceRules->isPudoServiceType($carrierService);
    }

    /**
     * @param CarrierService $service
     *
     * @return bool
     */
    private function isOohDeliveryType(CarrierService $service): bool
    {
        return $this->carrierServiceRules->isOohDeliveryOption($service);
    }

    /**
     * @param int $orderId
     *
     * @return bool
     */
    private function isHomeDeliveryType(int $orderId): bool
    {
        return null !== $this->samedayShippingHdAddressParser->parse($orderId);
    }
}
