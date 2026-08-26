<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Mappers;

final class GenerateAwbMapper
{
    public const ORDER_ID_KEY = 'samedaycourier-order-id';

    public const SERVICE_ID_KEY = 'samedaycourier-service';

    public const PICKUP_POINT_ID_KEY = 'samedaycourier-package-pickup-point';

    public const SHIPPING_LINES_KEY = 'shipping_lines';

    public const SHIPPING_KEY = 'shipping';

    public const BILLING_KEY = 'billing';

    public const LOCKER_KEY = 'locker';

    public const OPEN_PACKAGE_KEY = 'samedaycourier-open-package-status';

    public const LOCKER_FIRST_MILE_KEY = 'samedaycourier-locker_first_mile';

    public const PACKAGE_TYPE_KEY = 'samedaycourier-package-type';

    public const AWB_PAYMENT_KEY = 'samedaycourier-package-awb-payment';

    public const INSURANCE_VALUE_KEY = 'samedaycourier-package-insurance-value';

    public const REPAYMENT_KEY = 'samedaycourier-package-repayment';

    public const CLIENT_REFERENCE_KEY = 'samedaycourier-client-reference';

    public const OBSERVATION_KEY = 'samedaycourier-package-observation';

    public const PACKAGE_DIMENSIONS_KEY = 'samedaycourier-package-dimensions';

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
     * @return int
     */
    public function serviceId(): int
    {
        return (int) ($this->inputParams[self::SERVICE_ID_KEY] ?? 0);
    }

    /**
     * @return int
     */
    public function pickupPointId(): int
    {
        return (int) ($this->inputParams[self::PICKUP_POINT_ID_KEY] ?? 0);
    }

    /**
     * @return array
     */
    public function shippingLines(): array
    {
        return (array) ($this->inputParams[self::SHIPPING_LINES_KEY] ?? []);
    }

    /**
     * @return array
     */
    public function shipping(): array
    {
        return (array) ($this->inputParams[self::SHIPPING_KEY] ?? []);
    }

    /**
     * @return array
     */
    public function billing(): array
    {
        return (array) ($this->inputParams[self::BILLING_KEY] ?? []);
    }

    /**
     * @return mixed
     */
    public function locker()
    {
        return $this->inputParams[self::LOCKER_KEY] ?? null;
    }

    /**
     * @return bool
     */
    public function hasOpenPackage(): bool
    {
        return isset($this->inputParams[self::OPEN_PACKAGE_KEY]);
    }

    /**
     * @return bool
     */
    public function hasLockerFirstMile(): bool
    {
        return isset($this->inputParams[self::LOCKER_FIRST_MILE_KEY]);
    }

    /**
     * @return int
     */
    public function packageType(): int
    {
        return (int) ($this->inputParams[self::PACKAGE_TYPE_KEY] ?? 0);
    }

    /**
     * @return int
     */
    public function awbPayment(): int
    {
        return (int) ($this->inputParams[self::AWB_PAYMENT_KEY] ?? 0);
    }

    /**
     * @return float
     */
    public function insuranceValue(): float
    {
        return $this->toFloat($this->inputParams[self::INSURANCE_VALUE_KEY] ?? 0);
    }

    /**
     * @return float
     */
    public function repayment(): float
    {
        return $this->toFloat($this->inputParams[self::REPAYMENT_KEY] ?? 0);
    }

    /**
     * @return string|null
     */
    public function clientReference(): ?string
    {
        $clientReference = $this->inputParams[self::CLIENT_REFERENCE_KEY] ?? null;

        return null !== $clientReference ? (string) $clientReference : null;
    }

    /**
     * @return string|null
     */
    public function observation(): ?string
    {
        $observation = $this->inputParams[self::OBSERVATION_KEY] ?? null;

        return null !== $observation ? (string) $observation : null;
    }

    /**
     * @return array
     */
    public function packageDimensions(): array
    {
        return (array) ($this->inputParams[self::PACKAGE_DIMENSIONS_KEY] ?? []);
    }

    /**
     * @param mixed $value
     *
     * @return float
     */
    private function toFloat($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }
}
