<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers;

final class EditServiceMapper
{
    public const ID_KEY = 'samedaycourier-service-id';

    public const NAME_KEY = 'samedaycourier-service-name';

    public const PRICE_KEY = 'samedaycourier-price';

    public const PRICE_FREE_KEY = 'samedaycourier-free-delivery-price';

    public const STATUS_KEY = 'samedaycourier-status';

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
    public function id(): int
    {
        return (int) ($this->inputParams[self::ID_KEY] ?? 0);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return (string) ($this->inputParams[self::NAME_KEY] ?? '');
    }

    /**
     * @return string
     */
    public function price(): string
    {
        return (string) ($this->inputParams[self::PRICE_KEY] ?? '');
    }

    /**
     * @return string|null
     */
    public function priceFree(): ?string
    {
        $priceFree = $this->inputParams[self::PRICE_FREE_KEY] ?? null;

        return null !== $priceFree && '' !== $priceFree ? (string) $priceFree : null;
    }

    /**
     * @return string|null
     */
    public function status(): ?string
    {
        $status = $this->inputParams[self::STATUS_KEY] ?? null;

        return null !== $status ? (string) $status : null;
    }
}
