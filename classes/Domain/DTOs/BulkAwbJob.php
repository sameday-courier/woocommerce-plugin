<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

if (!defined('ABSPATH')) {
    exit;
}

final class BulkAwbJob
{
    private const STATUS_SUCCESS = 'success';

    private const STATUS_ERROR = 'error';

    private string $jobId;

    private int $userId;

    /**
     * @var BulkAwbJobOrderEntry[]
     */
    private array $orders;

    /**
     * @param BulkAwbJobOrderEntry[] $orders
     */
    public function __construct(string $jobId, int $userId, array $orders)
    {
        $this->jobId = $jobId;
        $this->userId = $userId;
        $this->orders = $orders;
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
     * @return BulkAwbJobOrderEntry[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    public function getTotal(): int
    {
        return count($this->orders);
    }

    public function getProcessedCount(): int
    {
        $processed = 0;
        foreach ($this->orders as $order) {
            if ($order->isProcessed()) {
                ++$processed;
            }
        }

        return $processed;
    }

    public function getSuccessCount(): int
    {
        $count = 0;
        foreach ($this->orders as $order) {
            $payload = $order->getPayload();
            if (null !== $payload && self::STATUS_SUCCESS === ($payload['status'] ?? null)) {
                ++$count;
            }
        }

        return $count;
    }

    public function getErrorCount(): int
    {
        $count = 0;
        foreach ($this->orders as $order) {
            $payload = $order->getPayload();
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

    public function getNextUnprocessed(): ?BulkAwbJobOrderEntry
    {
        foreach ($this->orders as $order) {
            if (!$order->isProcessed()) {
                return $order;
            }
        }

        return null;
    }

    /**
     * @param array{status: string, message: string} $payload
     */
    public function withOrderPayload(int $orderId, array $payload): self
    {
        $orders = [];
        foreach ($this->orders as $order) {
            if ($order->getOrderId() === $orderId) {
                $orders[] = $order->withPayload($payload);
                continue;
            }

            $orders[] = $order;
        }

        return new self($this->jobId, $this->userId, $orders);
    }

    /**
     * @return array{
     *     jobId: string,
     *     userId: int,
     *     orders: array<int, array{orderId: int, payload: array{status: string, message: string}|null}>
     * }
     */
    public function toArray(): array
    {
        return [
            'jobId' => $this->jobId,
            'userId' => $this->userId,
            'orders' => array_map(
                static function (BulkAwbJobOrderEntry $order): array {
                    return $order->toArray();
                },
                $this->orders
            ),
        ];
    }

    /**
     * @return array<int, array{orderId: int, status: string|null, message: string|null, awbNumber: string|null}>
     */
    public function toReportOrders(): array
    {
        $report = [];
        foreach ($this->orders as $order) {
            $payload = $order->getPayload();
            $report[] = [
                'orderId' => $order->getOrderId(),
                'status' => $payload['status'] ?? null,
                'message' => $payload['message'] ?? null,
                'awbNumber' => $payload['awbNumber'] ?? null,
            ];
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $ordersData = $data['orders'] ?? [];
        if (!is_array($ordersData)) {
            $ordersData = [];
        }

        $orders = [];
        foreach ($ordersData as $orderData) {
            if (!is_array($orderData)) {
                continue;
            }

            $entry = BulkAwbJobOrderEntry::fromArray($orderData);
            if ($entry->getOrderId() <= 0) {
                continue;
            }

            $orders[] = $entry;
        }

        return new self(
            (string) ($data['jobId'] ?? ''),
            (int) ($data['userId'] ?? 0),
            $orders
        );
    }

    /**
     * @param int[] $orderIds
     */
    public static function create(string $jobId, int $userId, array $orderIds): self
    {
        $orders = [];
        foreach ($orderIds as $orderId) {
            $orders[] = new BulkAwbJobOrderEntry($orderId);
        }

        return new self($jobId, $userId, $orders);
    }
}
