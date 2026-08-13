<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate;

use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use SamedayCourier\Shipping\Domain\DTOs\BillingDto;
use SamedayCourier\Shipping\Domain\DTOs\LockerDto;
use SamedayCourier\Shipping\Domain\DTOs\ShippingDto;
use SamedayCourier\Shipping\Domain\Models\SamedayService;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\SamedayShippingHdAddressParserInterface;
use SamedayCourier\Shipping\Domain\Ports\StateCodeResolverInterface;
use SamedayCourier\Shipping\Domain\DTOs\OohDto;
use SamedayCourier\Shipping\Domain\DTOs\RecipientDto;
use SamedayCourier\Shipping\Domain\Resolvers\Awb\Generate\Responses\AwbGenerateRecipientResponse;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Domain\SamedayServiceRules;
use SamedayCourier\Shipping\Domain\ValueObject\Address\PostalCode;

class AwbGenerateRecipientResolver
{
    /**
     * @var SamedayServiceRules $samedayServiceRules
     */
    private SamedayServiceRules $samedayServiceRules;

    /**
     * @var SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser
     */
    private SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser;

    /**
     * @var StateCodeResolverInterface $stateCodeResolver
     */
    private StateCodeResolverInterface $stateCodeResolver;

    /**
     * @var CityPostalCodeProviderInterface
     */
    private CityPostalCodeProviderInterface $cityPostalCodeProvider;

    /**
     * @param SamedayServiceRules $samedayServiceRules
     * @param SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser
     * @param StateCodeResolverInterface $stateCodeResolver
     * @param CityPostalCodeProviderInterface $cityPostalCodeProvider
     */
    public function __construct(
        SamedayServiceRules $samedayServiceRules,
        SamedayShippingHdAddressParserInterface $samedayShippingHdAddressParser,
        StateCodeResolverInterface $stateCodeResolver,
        CityPostalCodeProviderInterface $cityPostalCodeProvider
    )
    {
        $this->samedayServiceRules = $samedayServiceRules;
        $this->samedayShippingHdAddressParser = $samedayShippingHdAddressParser;
        $this->stateCodeResolver = $stateCodeResolver;
        $this->cityPostalCodeProvider = $cityPostalCodeProvider;
    }

    /**
     * @param int $orderId
     * @param ShippingDto $shipping
     * @param BillingDto $billing
     * @param SamedayService $service
     * @param LockerDto|null $locker
     *
     * @return AwbGenerateRecipientResponse
     */
    public function resolve(
        int $orderId,
        ShippingDto $shipping,
        BillingDto $billing,
        SamedayService $service,
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
            $state,
            $country,
            $this->cityPostalCodeProvider
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
            $awbRecipient->setCity($post_meta_samedaycourier_address_hd['city']);
            $county = $this->stateCodeResolver->resolveNameFromCode(
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
     * @param SamedayService $samedayService
     *
     * @return bool
     */
    private function isEasyBoxServiceType(SamedayService $samedayService): bool
    {
        return $this->samedayServiceRules->isEasyBoxServiceType($samedayService);
    }

    /**
     * @param SamedayService $samedayService
     *
     * @return bool
     */
    private function isPudoServiceType(SamedayService $samedayService): bool
    {
        return $this->samedayServiceRules->isPudoServiceType($samedayService);
    }

    /**
     * @param SamedayService $service
     *
     * @return bool
     */
    private function isOohDeliveryType(SamedayService $service): bool
    {
        return $this->samedayServiceRules->isOohDeliveryOption($service);
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
