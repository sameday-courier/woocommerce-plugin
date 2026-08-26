<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers;

final class DeletePickupPointMapper
{
    public const SAMEDAY_ID_KEY = 'sameday_id';

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
    public function samedayId(): int
    {
        return (int) ($this->inputParams[self::SAMEDAY_ID_KEY] ?? 0);
    }
}
