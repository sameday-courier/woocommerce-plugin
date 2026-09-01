<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers;

final class AddNewParcelAwbMapper
{
    public const ORDER_ID_KEY = 'samedaycourier-order-id';

    public const PARCEL_WEIGHT_KEY = 'samedaycourier-parcel-weight';

    public const PARCEL_WIDTH_KEY = 'samedaycourier-parcel-width';

    public const PARCEL_LENGTH_KEY = 'samedaycourier-parcel-length';

    public const PARCEL_HEIGHT_KEY = 'samedaycourier-parcel-height';

    public const PARCEL_OBSERVATION_KEY = 'samedaycourier-parcel-observation';

    public const PARCEL_IS_LAST_KEY = 'samedaycourier-parcel-is-last';

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

    /**
     * @return mixed
     */
    public function parcelWeight()
    {
        return $this->inputParams[self::PARCEL_WEIGHT_KEY] ?? null;
    }

    /**
     * @return mixed
     */
    public function parcelWidth()
    {
        return $this->inputParams[self::PARCEL_WIDTH_KEY] ?? null;
    }

    /**
     * @return mixed
     */
    public function parcelLength()
    {
        return $this->inputParams[self::PARCEL_LENGTH_KEY] ?? null;
    }

    /**
     * @return mixed
     */
    public function parcelHeight()
    {
        return $this->inputParams[self::PARCEL_HEIGHT_KEY] ?? null;
    }

    /**
     * @return string
     */
    public function parcelObservation(): string
    {
        return (string) ($this->inputParams[self::PARCEL_OBSERVATION_KEY] ?? '');
    }

    /**
     * @return bool
     */
    public function parcelIsLast(): bool
    {
        return (bool) (int) ($this->inputParams[self::PARCEL_IS_LAST_KEY] ?? 0);
    }
}
