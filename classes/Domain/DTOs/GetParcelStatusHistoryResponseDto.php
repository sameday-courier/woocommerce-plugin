<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use Sameday\Objects\ParcelStatusHistory\ExpeditionObject;
use Sameday\Objects\ParcelStatusHistory\HistoryObject;
use Sameday\Objects\ParcelStatusHistory\SummaryObject;

final class GetParcelStatusHistoryResponseDto
{
    private SummaryObject $summary;

    /**
     * @var HistoryObject[]
     */
    private array $history;

    private ExpeditionObject $expeditionStatus;

    /**
     * @param HistoryObject[] $history
     */
    public function __construct(
        SummaryObject $summary,
        array $history,
        ExpeditionObject $expeditionStatus
    ) {
        $this->summary = $summary;
        $this->history = $history;
        $this->expeditionStatus = $expeditionStatus;
    }

    public function getSummary(): SummaryObject
    {
        return $this->summary;
    }

    /**
     * @return HistoryObject[]
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    public function getExpeditionStatus(): ExpeditionObject
    {
        return $this->expeditionStatus;
    }
}
