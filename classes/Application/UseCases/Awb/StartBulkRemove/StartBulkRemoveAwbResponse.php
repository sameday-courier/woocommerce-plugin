<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove;

use SamedayCourier\Shipping\Application\Common\Interfaces\ResponseInterface;
use SamedayCourier\Shipping\Application\Common\Traits\NoticerTrait;

final class StartBulkRemoveAwbResponse implements ResponseInterface
{
    use NoticerTrait;

    private ?string $jobId;

    private int $total;

    private int $processed;

    private bool $done;

    /**
     * @param ?string $noticeMessage
     * @param string $noticeType
     * @param ?string $jobId
     * @param int $total
     * @param int $processed
     * @param bool $done
     */
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

    /**
     * @return ?string
     */
    public function getJobId(): ?string
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
