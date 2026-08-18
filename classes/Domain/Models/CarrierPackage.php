<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Models;

final class CarrierPackage implements ModelInterface
{
    public int $orderId;
    public ?string $awbParcel;
    public ?string $summary;
    public ?string $history;
    public ?string $expeditionStatus;
    public ?string $sync;

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
    public function getAwbParcel(): ?string
    {
        return $this->awbParcel;
    }

    /**
     * @param string|null $awbParcel
     *
     * @return self
     */
    public function setAwbParcel(?string $awbParcel): self
    {
        $this->awbParcel = $awbParcel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @param string|null $summary
     *
     * @return self
     */
    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHistory(): ?string
    {
        return $this->history;
    }

    /**
     * @param string|null $history
     *
     * @return self
     */
    public function setHistory(?string $history): self
    {
        $this->history = $history;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExpeditionStatus(): ?string
    {
        return $this->expeditionStatus;
    }

    /**
     * @param string|null $expeditionStatus
     *
     * @return self
     */
    public function setExpeditionStatus(?string $expeditionStatus): self
    {
        $this->expeditionStatus = $expeditionStatus;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSync(): ?string
    {
        return $this->sync;
    }

    /**
     * @param string|null $sync
     *
     * @return self
     */
    public function setSync(?string $sync): self
    {
        $this->sync = $sync;

        return $this;
    }
}
