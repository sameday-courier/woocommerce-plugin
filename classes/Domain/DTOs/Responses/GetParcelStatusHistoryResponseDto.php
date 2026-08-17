<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class GetParcelStatusHistoryResponseDto
{
    /**
     * @var mixed
     */
    private $summary;

    /**
     * @var array<int, mixed>
     */
    private array $history;

    /**
     * @var mixed
     */
    private $expeditionStatus;

    /**
     * @param mixed $summary
     * @param array<int, mixed> $history
     * @param mixed $expeditionStatus
     */
    public function __construct($summary, array $history, $expeditionStatus)
    {
        $this->summary = $summary;
        $this->history = $history;
        $this->expeditionStatus = $expeditionStatus;
    }

    /**
     * @return mixed
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return array<int, mixed>
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * @return mixed
     */
    public function getExpeditionStatus()
    {
        return $this->expeditionStatus;
    }
}
