<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers;

final class ShowHistoryAwbMapper
{
    public const ORDER_ID_KEY = 'order-id';

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
    public function orderId(): int
    {
        return (int) ($this->inputParams[self::ORDER_ID_KEY] ?? 0);
    }
}
