<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use SamedayCourier\Shipping\Domain\ValueObject\BulkJobId;

final class BulkJobDto
{
    private const STATUS_SUCCESS = 'success';

    private const STATUS_ERROR = 'error';

    private BulkJobId $jobId;

    private int $userId;

    /**
     * @var BulkJobItemDto[]
     */
    private array $items;

    /**
     * @param BulkJobId $jobId
     * @param int $userId
     * @param BulkJobItemDto[] $items
     */
    public function __construct(BulkJobId $jobId, int $userId, array $items)
    {
        $this->jobId = $jobId;
        $this->userId = $userId;
        $this->items = $items;
    }

    /**
     * @return BulkJobId
     */
    public function getJobId(): BulkJobId
    {
        return $this->jobId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return BulkJobItemDto[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return count($this->items);
    }

    /**
     * @return int
     */
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

    /**
     * @return int
     */
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

    /**
     * @return int
     */
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

    /**
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->getProcessedCount() === $this->getTotal();
    }

    /**
     * @return ?BulkJobItemDto
     */
    public function getNextUnprocessed(): ?BulkJobItemDto
    {
        foreach ($this->items as $item) {
            if (!$item->isProcessed()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param int $itemId
     * @param array $payload
     *
     * @return self
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
     * @return array
     */
    public function toArray(): array
    {
        return [
            'jobId' => $this->jobId->toString(),
            'userId' => $this->userId,
            'items' => array_map(
                /**
                 * @param BulkJobItemDto $item
                 *
                 * @return array
                 */
                static function (BulkJobItemDto $item): array {
                    return $item->toArray();
                },
                $this->items
            ),
        ];
    }

    /**
     * @return array
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
     * @param array $data
     *
     * @return self
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

            $entry = BulkJobItemDto::fromArray($itemData);
            if ($entry->getItemId() <= 0) {
                continue;
            }

            $items[] = $entry;
        }

        return new self(
            BulkJobId::fromString((string) ($data['jobId'] ?? '')),
            (int) ($data['userId'] ?? 0),
            $items
        );
    }

    /**
     * @param BulkJobId $jobId
     * @param int $userId
     * @param int[] $itemIds
     *
     * @return self
     */
    public static function create(BulkJobId $jobId, int $userId, array $itemIds): self
    {
        $items = [];
        foreach ($itemIds as $itemId) {
            $items[] = new BulkJobItemDto($itemId);
        }

        return new self($jobId, $userId, $items);
    }
}
