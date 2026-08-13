<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Models;

final class SamedayService implements ModelInterface
{
    public int $id;
    public int $sameday_id;
    public ?string $samedayName;
    public ?bool $isTesting;
    public ?string $name;
    public ?float $price;
    public ?float $priceFree;
    public bool $status;
    public string $samedayCode;
    public ?string $serviceOptionalTaxes;

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
    public function getSamedayId(): int
    {
        return $this->sameday_id;
    }

    /**
     * @param int $sameday_id
     *
     * @return self
     */
    public function setSamedayId(int $sameday_id): self
    {
        $this->sameday_id = $sameday_id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSamedayName(): ?string
    {
        return $this->samedayName;
    }

    /**
     * @param string|null $samedayName
     *
     * @return self
     */
    public function setSamedayName(?string $samedayName): self
    {
        $this->samedayName = $samedayName;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsTesting(): ?bool
    {
        return $this->isTesting;
    }

    /**
     * @param bool|null $isTesting
     *
     * @return self
     */
    public function setIsTesting(?bool $isTesting): self
    {
        $this->isTesting = $isTesting;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     *
     * @return self
     */
    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPriceFree(): ?float
    {
        return $this->priceFree;
    }

    /**
     * @param float|null $priceFree
     *
     * @return self
     */
    public function setPriceFree(?float $priceFree): self
    {
        $this->priceFree = $priceFree;

        return $this;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     *
     * @return self
     */
    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getSamedayCode(): string
    {
        return $this->samedayCode;
    }

    /**
     * @param string $samedayCode
     *
     * @return self
     */
    public function setSamedayCode(string $samedayCode): self
    {
        $this->samedayCode = $samedayCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getServiceOptionalTaxes(): ?string
    {
        return $this->serviceOptionalTaxes;
    }

    /**
     * @param string|null $serviceOptionalTaxes
     *
     * @return self
     */
    public function setServiceOptionalTaxes(?string $serviceOptionalTaxes): self
    {
        $this->serviceOptionalTaxes = $serviceOptionalTaxes;

        return $this;
    }
}
