<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class StartBulkRemoveAwbResponseDto
{
    private bool $success;

    private ?string $message;

    private ?string $jobId;

    private int $total;

    private int $processed;

    private bool $done;

    public function __construct(
        bool $success,
        ?string $message = null,
        ?string $jobId = null,
        int $total = 0,
        int $processed = 0,
        bool $done = false
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->jobId = $jobId;
        $this->total = $total;
        $this->processed = $processed;
        $this->done = $done;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getJobId(): ?string
    {
        return $this->jobId;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getProcessed(): int
    {
        return $this->processed;
    }

    public function isDone(): bool
    {
        return $this->done;
    }
}
