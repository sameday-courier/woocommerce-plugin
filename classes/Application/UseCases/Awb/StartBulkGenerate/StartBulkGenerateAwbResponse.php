<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class StartBulkGenerateAwbResponse implements ResponseInterface
{
    use NoticerTrait;

    private ?string $jobId;

    private int $total;

    private int $processed;

    private bool $done;

    public function __construct(
        ?string $noticeMessage,
        string $noticeType,
        ?string $jobId = null,
        int $total = 0,
        int $processed = 0,
        bool $done = false
    ) {
        $this->noticeMessage = $noticeMessage;
        $this->noticeType = $noticeType;
        $this->jobId = $jobId;
        $this->total = $total;
        $this->processed = $processed;
        $this->done = $done;
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
