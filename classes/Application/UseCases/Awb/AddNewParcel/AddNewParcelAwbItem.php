<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use Sameday\Objects\ParcelDimensionsObject;

if (!defined('ABSPATH')) {
    exit;
}

class AddNewParcelAwbItem
{
    private ParcelDimensionsObject $parcelDimensionsObject;
    private string $parcelObservation;

    private bool $parcelIsLast;

    public function __construct(
        ParcelDimensionsObject $parcelDimensionsObject,
        string $parcelObservation = "",
        bool $parcelIsLast = false
    )
    {
        $this->parcelDimensionsObject = $parcelDimensionsObject;
        $this->parcelObservation = $parcelObservation;
        $this->parcelIsLast = $parcelIsLast;
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
