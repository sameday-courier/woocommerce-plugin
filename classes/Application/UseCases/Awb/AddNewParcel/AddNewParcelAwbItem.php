<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use Sameday\Objects\ParcelDimensionsObject;
use SamedayCourier\Shipping\Application\Common\Factories\ParcelDimensionsFactory;
use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class AddNewParcelAwbItem implements ItemInterface
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
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        $parcelDimensionsFactory = new ParcelDimensionsFactory();
        $parcelDimensionObject = $parcelDimensionsFactory->fromAttributes(
            $inputParams['samedaycourier-parcel-weight'],
            $inputParams['samedaycourier-parcel-width'],
            $inputParams['samedaycourier-parcel-length'],
            $inputParams['samedaycourier-parcel-height'],
        );

        return new self(
            (int) ($inputParams['samedaycourier-order-id'] ?? 0),
            $parcelDimensionObject,
            $inputParams['samedaycourier-parcel-observation'] ?? '',
            $inputParams['samedaycourier-parcel-is-last'] ?? false
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
