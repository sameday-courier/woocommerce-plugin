<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class PostParcelRequestDto
{
    private string $awbNumber;

    /**
     * @var mixed
     */
    private $parcelWeight;

    /**
     * @var mixed
     */
    private $parcelWidth;

    /**
     * @var mixed
     */
    private $parcelLength;

    /**
     * @var mixed
     */
    private $parcelHeight;

    private int $position;

    private ?string $observation;

    private ?string $priceObservation;

    private bool $last;

    /**
     * @param mixed $parcelWeight
     * @param mixed $parcelWidth
     * @param mixed $parcelLength
     * @param mixed $parcelHeight
     */
    public function __construct(
        string $awbNumber,
        $parcelWeight,
        $parcelWidth,
        $parcelLength,
        $parcelHeight,
        int $position,
        ?string $observation = null,
        ?string $priceObservation = null,
        bool $last = false
    ) {
        $this->awbNumber = $awbNumber;
        $this->parcelWeight = $parcelWeight;
        $this->parcelWidth = $parcelWidth;
        $this->parcelLength = $parcelLength;
        $this->parcelHeight = $parcelHeight;
        $this->position = $position;
        $this->observation = $observation;
        $this->priceObservation = $priceObservation;
        $this->last = $last;
    }

    public function getAwbNumber(): string
    {
        return $this->awbNumber;
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
