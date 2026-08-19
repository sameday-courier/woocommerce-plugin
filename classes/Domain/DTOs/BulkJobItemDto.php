<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class BulkJobItemDto
{
    private int $itemId;

    /**
     * @var array{status: string, message: string, ...}|null
     */
    private ?array $payload;

    /**
     * @param int $itemId
     * @param ?array $payload
     */
    public function __construct(int $itemId, ?array $payload = null)
    {
        $this->itemId = $itemId;
        $this->payload = $payload;
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @return array{status:
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * @return bool
     */
    public function isProcessed(): bool
    {
        return null !== $this->payload;
    }

    /**
     * @param array $payload
     *
     * @return self
     */
    public function withPayload(array $payload): self
    {
        return new self($this->itemId, $payload);
    }

    /**
     * @return array{itemId:
     */
    public function toArray(): array
    {
        return [
            'itemId' => $this->itemId,
            'payload' => $this->payload,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $payload = $data['payload'] ?? null;
        if (!is_array($payload)) {
            $payload = null;
        }

        return new self(
            (int) ($data['itemId'] ?? $data['orderId'] ?? 0),
            $payload
        );
    }
}
