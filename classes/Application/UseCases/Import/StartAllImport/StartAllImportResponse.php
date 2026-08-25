<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Import\StartAllImport;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;
use SamedayCourier\Shipping\Domain\ValueObject\BulkJobId;

final class StartAllImportResponse implements ResponseInterface
{
    use NoticerTrait;

    private ?BulkJobId $jobId;

    private int $total;

    private int $processed;

    private bool $done;

    /**
     * @param ?string $noticeMessage
     * @param string $noticeType
     * @param ?BulkJobId $jobId
     * @param int $total
     * @param int $processed
     * @param bool $done
     */
    public function __construct(
        ?string $noticeMessage,
        string $noticeType,
        ?BulkJobId $jobId = null,
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

    /**
     * @return ?BulkJobId
     */
    public function getJobId(): ?BulkJobId
    {
        return $this->jobId;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getProcessed(): int
    {
        return $this->processed;
    }

    /**
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->done;
    }
}
