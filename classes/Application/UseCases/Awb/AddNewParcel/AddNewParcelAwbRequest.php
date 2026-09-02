<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;

final class AddNewParcelAwbRequest implements RequestInterface
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
        string $parcelObservation = '',
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
