<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Edit;

use SamedayCourier\Shipping\Application\Common\Interfaces\ItemInterface;

final class EditServiceItem implements ItemInterface
{
    /**
     * @var int $id
     */
    private int $id;

    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var string $price
     */
    private string $price;

    /**
     * @var string|null $priceFree
     */
    private ?string $priceFree;

    /**
     * @var string|null $status
     */
    private ?string $status;

    /**
     * @param int $id
     * @param string $name
     * @param string $price
     * @param string|null $priceFree
     * @param string|null $status
     */
    public function __construct(
        int $id,
        string $name,
        string $price,
        ?string $priceFree,
        ?string $status
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->priceFree = $priceFree;
        $this->status = $status;
    }

    /**
     * @param array $inputParams
     *
     * @return self
     */
    public static function fromArray(array $inputParams): self
    {
        $priceFree = $inputParams['samedaycourier-free-delivery-price'] ?? null;
        $status = $inputParams['samedaycourier-status'] ?? null;

        return new self(
            (int) ($inputParams['samedaycourier-service-id'] ?? 0),
            (string) ($inputParams['samedaycourier-service-name'] ?? ''),
            (string) ($inputParams['samedaycourier-price'] ?? ''),
            null !== $priceFree && '' !== $priceFree ? (string) $priceFree : null,
            null !== $status ? (string) $status : null,
        );
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @return string|null
     */
    public function getPriceFree(): ?string
    {
        return $this->priceFree;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }
}
