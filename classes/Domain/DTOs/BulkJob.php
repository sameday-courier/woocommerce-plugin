<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class BulkJob
{
    private const STATUS_SUCCESS = 'success';

    private const STATUS_ERROR = 'error';

    private string $jobId;

    private int $userId;

    /**
     * @var BulkJobItem[]
     */
    private array $items;

    /**
     * @param BulkJobItem[] $items
     */
    public function __construct(string $jobId, int $userId, array $items)
    {
        $this->jobId = $jobId;
        $this->userId = $userId;
        $this->items = $items;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return BulkJobItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): int
    {
        return count($this->items);
    }

    public function getProcessedCount(): int
    {
        $processed = 0;
        foreach ($this->items as $item) {
            if ($item->isProcessed()) {
                ++$processed;
            }
        }

        return $processed;
    }

    public function getSuccessCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            $payload = $item->getPayload();
            if (null !== $payload && self::STATUS_SUCCESS === ($payload['status'] ?? null)) {
                ++$count;
            }
        }

        return $count;
    }

    public function getErrorCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            $payload = $item->getPayload();
            if (null !== $payload && self::STATUS_ERROR === ($payload['status'] ?? null)) {
                ++$count;
            }
        }

        return $count;
    }

    public function isDone(): bool
    {
        return $this->getProcessedCount() === $this->getTotal();
    }

    public function getNextUnprocessed(): ?BulkJobItem
    {
        foreach ($this->items as $item) {
            if (!$item->isProcessed()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param array{status: string, message: string, ...} $payload
     */
    public function withItemPayload(int $itemId, array $payload): self
    {
        $items = [];
        foreach ($this->items as $item) {
            if ($item->getItemId() === $itemId) {
                $items[] = $item->withPayload($payload);
                continue;
            }

            $items[] = $item;
        }

        return new self($this->jobId, $this->userId, $items);
    }

    /**
     * @return array{
     *     jobId: string,
     *     userId: int,
     *     items: array<int, array{itemId: int, payload: array{status: string, message: string, ...}|null}>
     * }
     */
    public function toArray(): array
    {
        return [
            'jobId' => $this->jobId,
            'userId' => $this->userId,
            'items' => array_map(
                static function (BulkJobItem $item): array {
                    return $item->toArray();
                },
                $this->items
            ),
        ];
    }

    /**
     * @return array<int, array{itemId: int, status: string|null, message: string|null, ...}>
     */
    public function toReportItems(): array
    {
        $report = [];
        foreach ($this->items as $item) {
            $payload = $item->getPayload() ?? [];
            $report[] = array_merge(
                [
                    'itemId' => $item->getItemId(),
                    'status' => $payload['status'] ?? null,
                    'message' => $payload['message'] ?? null,
                ],
                $payload
            );
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $itemsData = $data['items'] ?? $data['orders'] ?? [];
        if (!is_array($itemsData)) {
            $itemsData = [];
        }

        $items = [];
        foreach ($itemsData as $itemData) {
            if (!is_array($itemData)) {
                continue;
            }

            $entry = BulkJobItem::fromArray($itemData);
            if ($entry->getItemId() <= 0) {
                continue;
            }

            $items[] = $entry;
        }

        return new self(
            (string) ($data['jobId'] ?? ''),
            (int) ($data['userId'] ?? 0),
            $items
        );
    }

    /**
     * @param int[] $itemIds
     */
    public static function create(string $jobId, int $userId, array $itemIds): self
    {
        $items = [];
        foreach ($itemIds as $itemId) {
            $items[] = new BulkJobItem($itemId);
        }

        return new self($jobId, $userId, $items);
    }
}
