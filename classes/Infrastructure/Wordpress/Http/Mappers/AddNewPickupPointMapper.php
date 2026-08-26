<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers;

final class AddNewPickupPointMapper
{
    public const COUNTRY_KEY = 'pickupPointCountry';

    public const COUNTY_KEY = 'pickupPointCounty';

    public const CITY_KEY = 'pickupPointCity';

    public const ADDRESS_KEY = 'pickupPointAddress';

    public const POSTAL_CODE_KEY = 'pickupPointPostalCode';

    public const ALIAS_KEY = 'pickupPointAlias';

    public const CONTACT_PERSON_NAME_KEY = 'pickupPointContactPersonName';

    public const CONTACT_PERSON_PHONE_KEY = 'pickupPointContactPersonPhone';

    public const DEFAULT_KEY = 'pickupPointDefault';

    /**
     * @var array $inputParams
     */
    private array $inputParams;

    /**
     * @param array $inputParams
     */
    public function __construct(array $inputParams)
    {
        $this->inputParams = $inputParams;
    }

    /**
     * @return string
     */
    public function pickupPointCountryId(): string
    {
        return (string) ($this->inputParams[self::COUNTRY_KEY] ?? '');
    }

    /**
     * @return string
     */
    public function pickupPointCountyId(): string
    {
        return (string) ($this->inputParams[self::COUNTY_KEY] ?? '');
    }

    /**
     * @return string
     */
    public function pickupPointCityId(): string
    {
        return (string) ($this->inputParams[self::CITY_KEY] ?? '');
    }

    /**
     * @return string
     */
    public function pickupPointAddress(): string
    {
        return (string) ($this->inputParams[self::ADDRESS_KEY] ?? '');
    }

    /**
     * @return string
     */
    public function pickupPointPostalCode(): string
    {
        return (string) ($this->inputParams[self::POSTAL_CODE_KEY] ?? '');
    }

    /**
     * @return string
     */
    public function pickupPointAlias(): string
    {
        return (string) ($this->inputParams[self::ALIAS_KEY] ?? '');
    }

    /**
     * @return string
     */
    public function pickupPointContactPersonName(): string
    {
        return (string) ($this->inputParams[self::CONTACT_PERSON_NAME_KEY] ?? '');
    }

    /**
     * @return string
     */
    public function pickupPointContactPersonPhone(): string
    {
        return (string) ($this->inputParams[self::CONTACT_PERSON_PHONE_KEY] ?? '');
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return (bool) ($this->inputParams[self::DEFAULT_KEY] ?? false);
    }
}
