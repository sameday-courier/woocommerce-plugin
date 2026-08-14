<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use Sameday\Objects\ParcelDimensionsObject;

final class PostParcelRequestDto
{
    private string $awbNumber;

    private ParcelDimensionsObject $parcelDimensions;

    private int $position;

    private ?string $observation;

    private ?string $priceObservation;

    private bool $last;

    public function __construct(
        string $awbNumber,
        ParcelDimensionsObject $parcelDimensions,
        int $position,
        ?string $observation = null,
        ?string $priceObservation = null,
        bool $last = false
    ) {
        $this->awbNumber = $awbNumber;
        $this->parcelDimensions = $parcelDimensions;
        $this->position = $position;
        $this->observation = $observation;
        $this->priceObservation = $priceObservation;
        $this->last = $last;
    }

    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }

    public function getParcelDimensions(): ParcelDimensionsObject
    {
        return $this->parcelDimensions;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function getPriceObservation(): ?string
    {
        return $this->priceObservation;
    }

    public function isLast(): bool
    {
        return $this->last;
    }
}
