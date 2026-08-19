<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

final class AddNewParcelAwbItem implements ItemInterface
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var mixed $parcelWeight
     */
    private $parcelWeight;

    /**
     * @var mixed $parcelWidth
     */
    private $parcelWidth;

    /**
     * @var mixed $parcelLength
     */
    private $parcelLength;

    /**
     * @var mixed $parcelHeight
     */
    private $parcelHeight;

    /**
     * @var string $parcelObservation
     */
    private string $parcelObservation;

    /**
     * @var bool $parcelIsLast
     */
    private bool $parcelIsLast;

    /**
     * @param int $orderId
     * @param mixed $parcelWeight
     * @param mixed $parcelWidth
     * @param mixed $parcelLength
     * @param mixed $parcelHeight
     * @param string $parcelObservation
     * @param bool $parcelIsLast
     */
    public function __construct(
        int $orderId,
        $parcelWeight,
        $parcelWidth,
        $parcelLength,
        $parcelHeight,
        string $parcelObservation = "",
        bool $parcelIsLast = false
    ) {
        $this->orderId = $orderId;
        $this->parcelWeight = $parcelWeight;
        $this->parcelWidth = $parcelWidth;
        $this->parcelLength = $parcelLength;
        $this->parcelHeight = $parcelHeight;
        $this->parcelObservation = $parcelObservation;
        $this->parcelIsLast = $parcelIsLast;
    }

    /**
     * @param array $inputParams
     *
     * @return self
     */
    /**
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        return new self(
            (int) ($inputParams['samedaycourier-order-id'] ?? 0),
            $inputParams['samedaycourier-parcel-weight'] ?? null,
            $inputParams['samedaycourier-parcel-width'] ?? null,
            $inputParams['samedaycourier-parcel-length'] ?? null,
            $inputParams['samedaycourier-parcel-height'] ?? null,
            (string) ($inputParams['samedaycourier-parcel-observation'] ?? ''),
            (bool) (int) ($inputParams['samedaycourier-parcel-is-last'] ?? 0)
        );
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return mixed
     */
    public function getParcelWeight()
    {
        return $this->parcelWeight;
    }

    /**
     * @return mixed
     */
    public function getParcelWidth()
    {
        return $this->parcelWidth;
    }

    /**
     * @return mixed
     */
    public function getParcelLength()
    {
        return $this->parcelLength;
    }

    /**
     * @return mixed
     */
    public function getParcelHeight()
    {
        return $this->parcelHeight;
    }

    /**
     * @return string
     */
    public function getParcelObservation(): string
    {
        return $this->parcelObservation;
    }

    /**
     * @return bool
     */
    public function isParcelIsLast(): bool
    {
        return $this->parcelIsLast;
    }
}
