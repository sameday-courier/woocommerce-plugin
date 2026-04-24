<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Models;

final class SamedayAwb implements ModelInterface
{
    public int $id;
    public int $orderId;
    public ?string $awbNumber;
    public ?string $parcels;
    public ?float $awbCost;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * 
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     * 
     * @return self
     */
    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAwbNumber(): ?string
    {
        return $this->awbNumber;
    }

    /**
     * @param string|null $awbNumber
     * 
     * @return self
     */
    public function setAwbNumber(?string $awbNumber): self
    {
        $this->awbNumber = $awbNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParcels(): ?string
    {
        return $this->parcels;
    }

    /**
     * @param string|null $parcels
     * 
     * @return self
     */
    public function setParcels(?string $parcels): self
    {
        $this->parcels = $parcels;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getAwbCost(): ?float
    {
        return $this->awbCost;
    }

    /**
     * @param float|null $awbCost
     * 
     * @return self
     */
    public function setAwbCost(?float $awbCost): self
    {
        $this->awbCost = $awbCost;

        return $this;
    }
}
