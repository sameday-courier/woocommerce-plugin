<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers;

final class ChangeLockerMapper
{
    public const ORDER_ID_KEY = 'orderId';

    public const LOCKER_KEY = 'locker';

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
        return isset($this->inputParams[self::ORDER_ID_KEY])
            ? (int) $this->inputParams[self::ORDER_ID_KEY]
            : 0;
    }

    /**
     * @return mixed
     */
    public function locker()
    {
        return $this->inputParams[self::LOCKER_KEY] ?? null;
    }
}
