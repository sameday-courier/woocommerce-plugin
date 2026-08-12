<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class BulkAwbJobOrderEntry
{
    private int $orderId;

    /**
     * @var array{status: string, message: string, awbNumber?: string|null}|null
     */
    private ?array $payload;

    /**
     * @param array{status: string, message: string, awbNumber?: string|null}|null $payload
     */
    public function __construct(int $orderId, ?array $payload = null)
    {
        $this->orderId = $orderId;
        $this->payload = $payload;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return array{status: string, message: string, awbNumber?: string|null}|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function isProcessed(): bool
    {
        return null !== $this->payload;
    }

    /**
     * @param array{status: string, message: string, awbNumber?: string|null} $payload
     */
    public function withPayload(array $payload): self
    {
        return new self($this->orderId, $payload);
    }

    /**
     * @return array{orderId: int, payload: array{status: string, message: string, awbNumber?: string|null}|null}
     */
    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'payload' => $this->payload,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $payload = $data['payload'] ?? null;
        if (!is_array($payload)) {
            $payload = null;
        }

        return new self(
            (int) ($data['orderId'] ?? 0),
            $payload
        );
    }
}
