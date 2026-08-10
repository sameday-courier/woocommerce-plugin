<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use Sameday\Objects\ParcelDimensionsObject;

if (!defined('ABSPATH')) {
    exit;
}

final class AddNewParcelAwbItem
{
    /**
     * @var int $orderId
     */
    private int $orderId;

    /**
     * @var ParcelDimensionsObject $parcelDimensionsObject
     */
    private ParcelDimensionsObject $parcelDimensionsObject;

    /**
     * @var string $parcelObservation
     */
    private string $parcelObservation;

    /**
     * @var bool $parcelIsLast
     */
    private bool $parcelIsLast;

    public function __construct(
        int $orderId,
        ParcelDimensionsObject $parcelDimensionsObject,
        string $parcelObservation = "",
        bool $parcelIsLast = false
    )
    {
        $this->orderId = $orderId;
        $this->parcelDimensionsObject = $parcelDimensionsObject;
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
     * @return ParcelDimensionsObject
     */
    public function getParcelDimensionsObject(): ParcelDimensionsObject
    {
        return $this->parcelDimensionsObject;
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
