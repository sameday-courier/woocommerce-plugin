<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers;

final class GetCitiesMapper
{
    public const COUNTY_ID_KEY = 'countyId';

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
     * @return int
     */
    public function countyId(): int
    {
        return (int) ($this->inputParams[self::COUNTY_ID_KEY] ?? 0);
    }
}
